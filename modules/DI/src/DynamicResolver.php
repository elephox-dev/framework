<?php
declare(strict_types=1);

namespace Elephox\DI;

use BadFunctionCallException;
use BadMethodCallException;
use Closure;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Enumerable;
use Elephox\DI\Contract\Resolver;
use Generator;
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
readonly class DynamicResolver implements Resolver
{
	private ResolverStack $resolverStack;

	public function __construct(
		private ServiceProvider $serviceProvider,
	) {
		$this->resolverStack = new ResolverStack();
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

	/**
	 * @template TResult
	 *
	 * @param ReflectionFunction|Closure|Closure(mixed): TResult $callback
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): (null|TResult) $onUnresolved
	 *
	 * @return TResult
	 *
	 * @throws BadFunctionCallException
	 */
	public function call(Closure|ReflectionFunction $callback, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed
	{
		/** @noinspection PhpUnhandledExceptionInspection $callback is never a string */
		$reflectionFunction = $callback instanceof ReflectionFunction ? $callback : new ReflectionFunction($callback);
		$arguments = $this->resolveArguments($reflectionFunction, $overrideArguments, $onUnresolved);

		/** @var TResult */
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
			if ($this->serviceProvider->has($name)) {
				/** @var class-string $name */
				return $this->serviceProvider->get($name);
			}

			if ($parameter->isDefaultValueAvailable()) {
				return $parameter->getDefaultValue();
			}

			throw new MissingTypeHintException($parameter);
		}

		if ($type instanceof ReflectionUnionType) {
			$extractTypeNames = static function (ReflectionUnionType|ReflectionIntersectionType $refType, callable $self): Enumerable {
				/** @var Enumerable<string|list<string>> */
				return new Enumerable(static function () use ($refType, $self) {
					/**
					 * @var Closure(ReflectionUnionType|ReflectionIntersectionType, Closure): GenericEnumerable<class-string> $self
					 * @var ReflectionType $t
					 */
					foreach ($refType->getTypes() as $t) {
						assert($t instanceof ReflectionType, '$t must be an instance of ReflectionType');

						if ($t instanceof ReflectionUnionType) {
							yield $self($t, $self)->toList();
						} elseif ($t instanceof ReflectionIntersectionType) {
							yield [$self($t, $self)->toList()];
						} elseif ($t instanceof ReflectionNamedType) {
							yield [$t->getName()];
						} else {
							throw new ReflectionException('Unsupported ReflectionType: ' . get_debug_type($t));
						}
					}
				});
			};

			$allowedTypes = $extractTypeNames($type, $extractTypeNames)->select(static function (string|array $t): string|array {
				if (!is_array($t)) {
					return $t;
				}

				if (count($t) === 1) {
					return $t[0];
				}

				return collect(...$t)->flatten()->toList();
			});
		} else {
			/** @var ReflectionNamedType $type */
			$allowedTypes = [$type->getName()];
		}

		/** @var list<class-string|list<class-string>> $allowedTypes */
		foreach ($allowedTypes as $typeName) {
			if (is_string($typeName)) {
				if (!$this->serviceProvider->has($typeName)) {
					continue;
				}

				return $this->resolveService($typeName, $parameter);
			}

			if (is_array($typeName)) {
				/** @var class-string $combinedTypeName */
				$combinedTypeName = implode('&', $typeName);

				if (!$this->serviceProvider->has($combinedTypeName)) {
					continue;
				}

				return $this->resolveService($combinedTypeName, $parameter);
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
	 * @param ReflectionParameter $parameter
	 *
	 * @return TService
	 */
	private function resolveService(string $name, ReflectionParameter $parameter): object
	{
		$this->resolverStack->push("$name::$parameter");

		$service = $this->serviceProvider->get($name);

		$this->resolverStack->pop();

		return $service;
	}
}
