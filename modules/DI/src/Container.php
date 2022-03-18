<?php
declare(strict_types=1);

namespace Elephox\DI;

use Closure;
use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use Elephox\DI\Contract\NotContainerSerializable;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;

class Container implements Contract\Container
{
	/** @var ArrayMap<string, Contract\Binding> */
	private ArrayMap $map;

	/** @var ArrayMap<string, non-empty-string> */
	private ArrayMap $aliases;

	public function __construct()
	{
		/** @var ArrayMap<string, Contract\Binding> */
		$this->map = new ArrayMap();

		/** @var ArrayMap<string, non-empty-string> */
		$this->aliases = new ArrayMap();

		$this->registerSelf();
	}

	private function registerSelf(): void
	{
		$this->register(self::class, $this, ServiceLifetime::Singleton, Contract\Container::class, 'container');
	}

	public function has(string $id): bool
	{
		return $this->map->has($id) || $this->aliases->has($id);
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|null|callable(Contract\Container): T $implementation
	 * @param ServiceLifetime $lifetime
	 * @param non-empty-string ...$aliases
	 */
	public function register(string $contract, callable|string|object|null $implementation = null, ServiceLifetime $lifetime = ServiceLifetime::Singleton, string ...$aliases): void
	{
		if ($implementation === null) {
			if (!class_exists($contract)) {
				throw new InvalidArgumentException("Class '$contract' does not exist");
			}

			$this->register($contract, $contract, $lifetime, ...$aliases);

			return;
		}

		$instance = null;

		/** @var callable(Contract\Container): T $builder */
		if (is_callable($implementation)) {
			$builder = $implementation;
		} else if (is_object($implementation)) {
			if ($lifetime !== ServiceLifetime::Singleton) {
				trigger_error("Instance lifetime '$lifetime->name' may not have the desired effect when using an object as the implementation. Consider using a callable instead.", E_USER_WARNING);
			}

			$builder = static fn(): object => $implementation;
			$instance = $implementation;
		} else {
			$builder = static fn(Contract\Container $container): object => $container->instantiate($implementation);
		}

		$binding = new Binding($builder, $lifetime, $instance);

		$this->map->put($contract, $binding);

		foreach ($aliases as $alias) {
			$this->aliases->put($alias, $contract);
		}
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|null|callable(Contract\Container): T $implementation
	 * @param non-empty-string ...$aliases
	 */
	public function singleton(string $contract, callable|string|object|null $implementation = null, string ...$aliases): void
	{
		$this->register($contract, $implementation, ServiceLifetime::Singleton, ...$aliases);
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|null|callable(Contract\Container): T $implementation
	 * @param non-empty-string ...$aliases
	 */
	public function transient(string $contract, callable|string|object|null $implementation = null, string ...$aliases): void
	{
		$this->register($contract, $implementation, ServiceLifetime::Transient, ...$aliases);
	}

	/**
	 * @template T
	 *
	 * @param class-string<T>|string $alias
	 *
	 * @return class-string<T>
	 */
	private function resolveAlias(string $alias): string
	{
		while (!$this->map->has($alias)) {
			if (!$this->aliases->has($alias)) {
				throw new UnresolvedParameterException($alias);
			}

			$alias = $this->aliases->get($alias);
		}

		/** @var class-string<T> $alias */
		return $alias;
	}

	/**
	 * @template T as object
	 *
	 * @param class-string<T>|string $id
	 *
	 * @return T
	 */
	public function get(string $id): object
	{
		$id = $this->resolveAlias($id);

		/** @var Contract\Binding<T> $binding */
		$binding = $this->map->get($id);

		$instance = match ($binding->getLifetime()) {
			ServiceLifetime::Transient => $this->buildTransientInstance($binding),
			ServiceLifetime::Singleton => $this->buildRequestInstance($binding),
		};

		if (!($instance instanceof $id)) {
			throw new InvalidBindingInstanceException($instance, $id);
		}

		return $instance;
	}

	/**
	 * @template T as object
	 *
	 * @param Contract\Binding<T> $binding
	 *
	 * @return T
	 */
	private function buildTransientInstance(Contract\Binding $binding): object
	{
		$builder = $binding->getBuilder();

		return $builder($this);
	}

	/**
	 * @template T as object
	 *
	 * @param Contract\Binding<T> $binding
	 *
	 * @return T
	 */
	private function buildRequestInstance(Contract\Binding $binding): object
	{
		$instance = $binding->getInstance();

		if ($instance === null) {
			$builder = $binding->getBuilder();
			$instance = $builder($this);

			$binding->setInstance($instance);
		}

		return $instance;
	}

	/**
	 * @template T as object
	 *
	 * @param class-string<T>|string $id
	 * @param array $overrideArguments
	 *
	 * @return T
	 * @throws ReflectionException
	 */
	public function instantiate(string $id, array $overrideArguments = []): object
	{
		// check if $id contains a class name
		if (!class_exists($id)) {
			try {
				// if not, check if $id is an alias (if not, this will throw)
				$id = $this->resolveAlias($id);
			} catch (UnresolvedParameterException $e) {
				throw new InvalidArgumentException("Class or alias $id does not exist", previous: $e);
			}
		}
		// $id is a valid class name and can be instantiated
		/** @var class-string<T> $id */

		$reflectionClass = new ReflectionClass($id);
		$constructor = $reflectionClass->getConstructor();
		if ($constructor === null) {
			return $reflectionClass->newInstance();
		}

		$arguments = $this->resolveArguments($constructor, $overrideArguments);

		return $reflectionClass->newInstanceArgs($arguments->toList());
	}

	/**
	 * @template T as object
	 *
	 * @param class-string<T>|non-empty-string $contract
	 * @param class-string<T>|T|null|callable(Contract\Container): T $implementation
	 * @param array $overrideArguments
	 * @param ServiceLifetime $lifetime
	 * @param non-empty-string ...$aliases
	 *
	 * @return T
	 */
	public function getOrRegister(string $contract, callable|string|object|null $implementation = null, array $overrideArguments = [], ServiceLifetime $lifetime = ServiceLifetime::Singleton, string ...$aliases): object
	{
		if (!$this->has($contract)) {
			/** @psalm-suppress ArgumentTypeCoercion */
			$this->register($contract, $implementation, $lifetime, ...$aliases);
		}

		/** @var T */
		return $this->get($contract);
	}

	/**
	 * @template T as object
	 *
	 * @param class-string<T>|non-empty-string $id
	 * @param array $overrideArguments
	 *
	 * @return T
	 * @throws ReflectionException
	 */
	public function getOrInstantiate(string $id, array $overrideArguments = []): object
	{
		if (!$this->has($id)) {
			return $this->instantiate($id, $overrideArguments);
		}

		return $this->get($id);
	}

	/**
	 * @template T as object
	 *
	 * @param class-string<T>|T $implementation
	 * @param array $properties
	 *
	 * @return T
	 * @throws ReflectionException
	 */
	public function restore(object|string $implementation, array $properties = []): object
	{
		if (is_string($implementation) && !class_exists($implementation)) {
			throw new InvalidArgumentException("Class $implementation does not exist");
		}

		$reflectionClass = new ReflectionClass($implementation);
		$instance = $reflectionClass->newInstanceWithoutConstructor();
		$classProperties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
		$defaultPropertyValues = $reflectionClass->getDefaultProperties();

		foreach ($classProperties as $classProperty) {
			if (array_key_exists($classProperty->getName(), $properties)) {
				$classProperty->setValue($instance, $properties[$classProperty->getName()]);
			} elseif (array_key_exists($classProperty->getName(), $defaultPropertyValues)) {
				$classProperty->setValue($instance, $defaultPropertyValues[$classProperty->getName()]);
			} else {
				/**
				 * @psalm-suppress UndefinedMethod
				 * @var class-string<T>|null $type
				 */
				$type = $classProperty->getType()?->getName();
				if ($type !== null && $this->has($type)) {
					$classProperty->setValue($instance, $this->get($type));
				}
			}
		}

		return $instance;
	}

	/**
	 * @template T as object
	 * @template TResult
	 *
	 * @param class-string<T>|T|non-empty-string $implementation
	 *
	 * @return TResult
	 * @throws ReflectionException
	 */
	public function call(string|object $implementation, string $method, array $overrideArguments = []): mixed
	{
		if (is_string($implementation)) {
			$object = $this->get($implementation);
		} else {
			/** @var T $implementation */
			$object = $implementation;
		}

		$reflectionClass = new ReflectionClass($object);
		$reflectionMethod = $reflectionClass->getMethod($method);
		$arguments = $this->resolveArguments($reflectionMethod, $overrideArguments);

		/** @var TResult */
		return $reflectionMethod->invokeArgs($object, $arguments->toList());
	}

	/**
	 * @template T as object
	 * @template TReturn
	 *
	 * @param class-string<T> $class
	 * @param callable(T): TReturn $callback
	 * @return TReturn
	 */
	public function tap(string $class, callable $callback): mixed
	{
		$object = $this->get($class);

		return $callback($object);
	}

	/**
	 * @template T as object
	 * @template TReturn
	 *
	 * @param class-string<T> $class
	 * @param callable(T): TReturn $callback
	 * @param TReturn|null $fallback
	 * @return TReturn|null
	 */
	public function tapOptional(string $class, callable $callback, mixed $fallback = null): mixed
	{
		if (!$this->has($class)) {
			/** @psalm-suppress MixedReturnStatement */
			return $fallback;
		}

		$object = $this->get($class);

		return $callback($object);
	}

	/**
	 * @template T as object
	 *
	 * @param Closure(): T $callback
	 * @param array $overrideArguments
	 *
	 * @return T
	 * @throws ReflectionException
	 */
	public function callback(Closure $callback, array $overrideArguments = []): mixed
	{
		$reflectionFunction = new ReflectionFunction($callback);
		$arguments = $this->resolveArguments($reflectionFunction, $overrideArguments);

		/** @var T */
		return $reflectionFunction->invokeArgs($arguments->toList());
	}

	private function resolveArguments(ReflectionFunctionAbstract $method, array $overrides): ArrayList
	{
		$values = new ArrayList();
		$parameters = $method->getParameters();

		$usedOverrides = 0;
		foreach ($parameters as $parameter) {
			if ($parameter->isVariadic()) {
				$values->addAll(array_slice($overrides, $usedOverrides));
				break;
			}

			if (array_key_exists($parameter->getName(), $overrides)) {
				$values->add($overrides[$parameter->getName()]);
				$usedOverrides++;
			} else {
				$values->add($this->resolveArgument($parameter));
			}
		}

		return $values;
	}

	private function resolveArgument(ReflectionParameter $parameter): mixed
	{
		$possibleArgument = null;
		if ($this->has($parameter->getName())) {
			/** @var mixed $possibleArgument */
			$possibleArgument = $this->get($parameter->getName());

			if (!$parameter->hasType()) {
				return $possibleArgument;
			}
		}

		$type = $parameter->getType();
		if ($type === null) {
			if ($parameter->isDefaultValueAvailable()) {
				return $parameter->getDefaultValue();
			}

			throw new MissingTypeHintException($parameter);
		}

		if ($type instanceof ReflectionUnionType) {
			$typeNames = array_map(static fn (ReflectionNamedType $t) => $t->getName(), $type->getTypes());
		} else {
			/**
			 * @psalm-suppress UndefinedMethod
			 */
			$typeNames = [$type->getName()];
		}

		if ($possibleArgument === null) {
			/**
			 * @var list<class-string> $typeNames
			 */
			foreach ($typeNames as $typeName) {
				if ($this->has($typeName)) {
					/** @var mixed $possibleArgument */
					$possibleArgument = $this->get($typeName);
					break;
				}
			}
		}

		if ($possibleArgument !== null) {
			if (empty(array_filter($typeNames, static fn (string $class) => $possibleArgument instanceof $class))) {
				$paramName = "$" . $parameter->getName();
				$possibleArgumentType = get_debug_type($possibleArgument);

				throw new LogicException("Argument $paramName was resolved to type $possibleArgumentType, which doesn't match the type hint $type");
			}

			return $possibleArgument;
		}

		if ($parameter->isDefaultValueAvailable()) {
			return $parameter->getDefaultValue();
		}

		if (!$parameter->allowsNull()) {
			throw new UnresolvedParameterException((string)$type, $parameter->name);
		}

		return null;
	}

	public function alias(string $alias, string $contract): void
	{
		$this->aliases->put($alias, $contract);
	}

	#[ArrayShape(['aliases' => "array", 'map' => "array"])]
	public function __serialize(): array
	{
		return [
			'aliases' => $this->aliases->toArray(),
			'map' => $this->map
				->whereKey(static fn(string $contract) => !$contract instanceof NotContainerSerializable)
				->where(static fn(Contract\Binding $binding) => $binding->getInstance() === null || !$binding->getInstance() instanceof NotContainerSerializable)
				->select(static fn(Contract\Binding $binding) => serialize($binding))
				->toArray(),
		];
	}

	public function __unserialize(array $data): void
	{
		if (!array_key_exists('aliases', $data)) {
			throw new InvalidArgumentException('Missing aliases key in serialized data');
		}

		if (!array_key_exists('map', $data)) {
			throw new InvalidArgumentException('Missing map key in serialized data');
		}

		$aliases = $data['aliases'];
		if (!is_array($aliases)) {
			throw new InvalidArgumentException('Aliases must be an array');
		}

		$map = $data['map'];
		if (!is_array($map)) {
			throw new InvalidArgumentException('Map must be an array');
		}

		/** @var ArrayMap<string, non-empty-string> */
		$this->aliases = new ArrayMap($aliases);

		/** @var ArrayMap<string, Contract\Binding> */
		$this->map = new ArrayMap();

		$this->registerSelf();

		/**
		 * @var non-empty-string $key
		 * @var non-empty-string $value
		 */
		foreach ($map as $key => $value) {
			/** @var Contract\Binding $binding */
			$binding = unserialize($value, ['allowed_classes' => [Binding::class]]);

			$this->map->put($key, $binding);
		}
	}
}
