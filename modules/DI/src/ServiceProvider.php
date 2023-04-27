<?php
declare(strict_types=1);

namespace Elephox\DI;

use BadFunctionCallException;
use BadMethodCallException;
use Closure;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\OffsetNotFoundException;
use Elephox\DI\Contract\RootServiceProvider;
use Elephox\DI\Contract\ServiceScope as ServiceScopeContract;
use Elephox\DI\Contract\ServiceScopeFactory;
use Generator;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

/**
 * @psalm-type argument-list = array<non-empty-string, mixed>
 */
readonly class ServiceProvider implements RootServiceProvider, ServiceScopeFactory
{
	/**
	 * @var ArrayMap<string, ServiceDescriptor>
	 */
	protected ArrayMap $descriptors;

	/** @var ArrayMap<class-string, object>
	 */
	protected ArrayMap $instances;

	private array $selfIds;
	private ResolverStack $resolverStack;

	/**
	 * @param iterable<ServiceDescriptor> $descriptors
	 * @param iterable<class-string, object> $instances
	 */
	public function __construct(iterable $descriptors = [], iterable $instances = [])
	{
		$this->descriptors = new ArrayMap();
		$this->instances = new ArrayMap();

		/** @var ServiceDescriptor $description */
		foreach ($descriptors as $descriptor) {
			$this->descriptors->put($descriptor->serviceType, $descriptor);
		}

		foreach ($instances as $className => $instance) {
			$this->instances->put($className, $instance);
		}

		$interfaces = class_implements($this);

		assert($interfaces !== false);

		$this->selfIds = [
			self::class,
			...$interfaces,
		];

		$this->resolverStack = new ResolverStack();
	}

	protected function isSelf(string $id): bool
	{
		return in_array($id, $this->selfIds, true);
	}

	public function has(string $id): bool
	{
		return $this->isSelf($id) || $this->descriptors->has($id);
	}

	/**
	 * @template TService of object
	 *
	 * @param string|class-string<TService> $id
	 *
	 * @return TService|null
	 */
	public function get(string $id): ?object
	{
		try {
			if ($id === '') {
				throw new InvalidArgumentException('Service name cannot be empty');
			}

			/** @var TService */
			return $this->require($id);
		} catch (ServiceNotFoundException) {
			return null;
		}
	}

	protected function getDescriptor(string $id): ServiceDescriptor
	{
		try {
			return $this->descriptors->get($id);
		} catch (OffsetNotFoundException $e) {
			throw new ServiceNotFoundException($id, previous: $e);
		}
	}

	/**
	 * @template TService of object
	 *
	 * @param string|class-string<TService> $id
	 *
	 * @return TService
	 */
	public function require(string $id): object
	{
		if ($this->isSelf($id)) {
			/** @var TService */
			return $this;
		}

		$descriptor = $this->getDescriptor($id);

		/** @var TService */
		return match ($descriptor->lifetime) {
			ServiceLifetime::Transient => $this->requireTransient($descriptor),
			ServiceLifetime::Singleton => $this->requireSingleton($descriptor),
			ServiceLifetime::Scoped => $this->requireScoped($descriptor),
			default => throw new LogicException("Invalid descriptor lifetime: {$descriptor->lifetime->name}"),
		};
	}

	protected function requireTransient(ServiceDescriptor $descriptor): object
	{
		assert($descriptor->lifetime === ServiceLifetime::Transient, sprintf('Expected %s lifetime, got: %s', ServiceLifetime::Transient->name, $descriptor->lifetime->name));

		return $this->createInstance($descriptor);
	}

	protected function requireSingleton(ServiceDescriptor $descriptor): object
	{
		assert($descriptor->lifetime === ServiceLifetime::Singleton, sprintf('Expected %s lifetime, got: %s', ServiceLifetime::Singleton->name, $descriptor->lifetime->name));

		return $this->getOrCreateInstance($descriptor);
	}

	protected function requireScoped(ServiceDescriptor $descriptor): object
	{
		assert($descriptor->lifetime === ServiceLifetime::Scoped, sprintf('Expected %s lifetime, got: %s', ServiceLifetime::Scoped->name, $descriptor->lifetime->name));

		throw new ServiceException(sprintf(
			"Cannot resolve service '%s' from %s, as it requires a scope.",
			$descriptor->serviceType,
			get_debug_type($this),
		));
	}

	protected function getOrCreateInstance(ServiceDescriptor $descriptor): object
	{
		if ($this->instances->has($descriptor->serviceType)) {
			return $this->instances->get($descriptor->serviceType);
		}

		$service = $this->createInstance($descriptor);

		$this->instances->put($descriptor->serviceType, $service);

		return $service;
	}

	protected function createInstance(ServiceDescriptor $descriptor): object
	{
		try {
			return $descriptor->createInstance($this);
		} catch (BadFunctionCallException $e) {
			throw new ServiceInstantiationException($descriptor->serviceType, previous: $e);
		}
	}

	public function createScope(): ServiceScopeContract
	{
		$scopedProvider = new ScopedServiceProvider(
			$this,
			$this->descriptors->where(static fn (ServiceDescriptor $d) => $d->lifetime === ServiceLifetime::Scoped),
		);

		return new ServiceScope($scopedProvider);
	}

	public function dispose(): void
	{
		$this->instances->clear();
	}

	public function instantiate(string $className, array $overrideArguments = [], ?Closure $onUnresolved = null): object
	{
		if (!class_exists($className)) {
			assert(is_string($className), sprintf('Expected string, got: %s', get_debug_type($className)));

			throw new ClassNotFoundException($className);
		}

		$reflectionClass = new ReflectionClass($className);
		$constructor = $reflectionClass->getConstructor();

		try {
			if ($constructor === null) {
				return $reflectionClass->newInstance();
			}

			$serviceName = $constructor->getDeclaringClass()->getName();

			$this->resolverStack->push("$serviceName::__construct");

			$arguments = $this->resolveArguments($constructor, $overrideArguments, $onUnresolved);
			$instance = $reflectionClass->newInstanceArgs([...$arguments]);

			$this->resolverStack->pop();

			return $instance;
		} catch (ReflectionException $e) {
			throw new BadMethodCallException("Failed to instantiate class '$className'", previous: $e);
		}
	}

	/**
	 * @param class-string $className
	 * @param non-empty-string $method
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @return mixed
	 *
	 * @throws BadMethodCallException
	 */
	public function callMethod(string $className, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed
	{
		$instance = $this->instantiate($className);

		return $this->callMethodOn($instance, $method, $overrideArguments, $onUnresolved);
	}

	/**
	 * @param non-empty-string $method
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @throws BadMethodCallException
	 */
	public function callMethodOn(object $instance, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed
	{
		try {
			$reflectionClass = new ReflectionClass($instance);
			$reflectionMethod = $reflectionClass->getMethod($method);
			$arguments = $this->resolveArguments($reflectionMethod, $overrideArguments, $onUnresolved);

			return $reflectionMethod->invokeArgs($instance, [...$arguments]);
		} catch (ReflectionException $e) {
			throw new BadMethodCallException(sprintf(
				"Failed to call method '%s' on class '%s'",
				$method,
				$instance::class,
			), previous: $e);
		}
	}

	/**
	 * @param class-string $className
	 * @param non-empty-string $method
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @throws BadMethodCallException
	 */
	public function callStaticMethod(string $className, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed
	{
		try {
			$reflectionClass = new ReflectionClass($className);
			$reflectionMethod = $reflectionClass->getMethod($method);
			$arguments = $this->resolveArguments($reflectionMethod, $overrideArguments, $onUnresolved);

			return $reflectionMethod->invokeArgs(null, [...$arguments]);
		} catch (ReflectionException $e) {
			throw new BadMethodCallException("Failed to call method '$method' on class '$className'", previous: $e);
		}
	}

	public function call(Closure|ReflectionFunction $callback, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed
	{
		/** @noinspection PhpUnhandledExceptionInspection $callback is never a string */
		$reflectionFunction = $callback instanceof ReflectionFunction ? $callback : new ReflectionFunction($callback);
		$arguments = $this->resolveArguments($reflectionFunction, $overrideArguments, $onUnresolved);

		return $reflectionFunction->invokeArgs([...$arguments]);
	}

	public function resolveArguments(ReflectionFunctionAbstract $function, array $overrideArguments = [], ?Closure $onUnresolved = null): Generator
	{
		if ($overrideArguments !== [] && array_is_list($overrideArguments)) {
			yield from $overrideArguments;

			return;
		}

		$argumentCount = 0;
		$parameters = $function->getParameters();

		foreach ($parameters as $parameter) {
			if ($parameter->isVariadic()) {
				yield from $overrideArguments;

				break;
			}

			$name = $parameter->getName();
			if (array_key_exists($name, $overrideArguments)) {
				yield $overrideArguments[$name];

				unset($overrideArguments[$name]);
			} else {
				try {
					yield $this->resolveArgument($parameter);
				} catch (UnresolvedParameterException $e) {
					if ($onUnresolved === null) {
						throw $e;
					}

					yield $onUnresolved($parameter, $argumentCount);
				}
			}

			$argumentCount++;
		}
	}

	private function resolveArgument(ReflectionParameter $parameter): mixed
	{
		$name = $parameter->getName();
		$type = $parameter->getType();

		if ($type === null) {
			if ($this->has($name)) {
				/** @var class-string $name */
				return $this->require($name);
			}

			if ($parameter->isDefaultValueAvailable()) {
				return $parameter->getDefaultValue();
			}

			throw new MissingTypeHintException($parameter);
		}

		if ($type instanceof ReflectionUnionType) {
			$extractTypeNames = static function (ReflectionUnionType|ReflectionIntersectionType $refType, callable $self): GenericEnumerable {
				return collect(...$refType->getTypes())
					->select(static function (mixed $t) use ($self): array {
						assert($t instanceof ReflectionType, '$t must be an instance of ReflectionType');

						/** @var Closure(ReflectionUnionType|ReflectionIntersectionType, Closure): GenericEnumerable<class-string> $self */
						if ($t instanceof ReflectionUnionType) {
							return $self($t, $self)->toList();
						}

						if ($t instanceof ReflectionIntersectionType) {
							return [$self($t, $self)->toList()];
						}

						if ($t instanceof ReflectionNamedType) {
							return [$t->getName()];
						}

						throw new ReflectionException('Unsupported ReflectionType: ' . get_debug_type($t));
					});
			};

			/** @psalm-suppress DocblockTypeContradiction */
			$allowedTypes = $extractTypeNames($type, $extractTypeNames)->select(static fn (string|array $t): string|array => is_array($t) ? collect(...$t)->flatten()->toList() : $t);
		} else {
			/** @var ReflectionNamedType $type */
			$allowedTypes = [$type->getName()];
		}

		/** @var list<class-string|list<class-string>> $allowedTypes */
		foreach ($allowedTypes as $typeName) {
			if (is_string($typeName)) {
				if (!$this->has($typeName)) {
					continue;
				}

				return $this->resolveService($typeName, $parameter->getDeclaringFunction()->getName());
			}

			if (is_array($typeName)) {
				/** @var class-string $combinedTypeName */
				$combinedTypeName = implode('&', $typeName);

				return $this->resolveService($combinedTypeName, $parameter->getDeclaringFunction()->getName());
			}
		}

		if ($parameter->isDefaultValueAvailable()) {
			return $parameter->getDefaultValue();
		}

		if ($parameter->allowsNull()) {
			return null;
		}

		throw new UnresolvedParameterException(
			$parameter->getDeclaringClass()?->getShortName() ??
			$parameter->getDeclaringFunction()->getClosureScopeClass()?->getShortName() ??
			'<unknown class>',
			$parameter->getDeclaringFunction()->getShortName(),
			(string) $type,
			$parameter->name,
			$parameter->getDeclaringFunction()->getFileName(),
			$parameter->getDeclaringFunction()->getStartLine(),
			$parameter->getDeclaringFunction()->getEndLine(),
		);
	}

	/**
	 * @template TService of object
	 *
	 * @param class-string<TService> $name
	 * @param string $forMethod
	 *
	 * @return TService
	 */
	private function resolveService(string $name, string $forMethod): object
	{
		$this->resolverStack->push("$name::$forMethod");

		$service = $this->require($name);

		$this->resolverStack->pop();

		return $service;
	}
}
