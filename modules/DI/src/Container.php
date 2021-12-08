<?php
declare(strict_types=1);

namespace Elephox\DI;

use Closure;
use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionProperty;

class Container implements Contract\Container
{
	/** @var \Elephox\Collection\ArrayMap<non-empty-string, Binding> */
	private ArrayMap $map;

	/** @var \Elephox\Collection\ArrayMap<non-empty-string, non-empty-string> */
	private ArrayMap $aliases;

	public function __construct()
	{
		$this->map = new ArrayMap();
		$this->aliases = new ArrayMap();

		$this->register(Contract\Container::class, $this, InstanceLifetime::Singleton, __CLASS__, 'container');
	}

	#[Pure] public function has(string $id): bool
	{
		return $this->map->has($id) || $this->aliases->has($id);
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|null|callable(Contract\Container): T $implementation
	 * @param InstanceLifetime $lifetime
	 * @param non-empty-string ...$aliases
	 */
	public function register(string $contract, callable|string|object|null $implementation = null, InstanceLifetime $lifetime = InstanceLifetime::Singleton, string ...$aliases): void
	{
		if ($implementation === null) {
			if (!class_exists($contract)) {
				throw new InvalidArgumentException("Class $contract does not exist");
			}

			self::register($contract, $contract, $lifetime, ...$aliases);

			return;
		}

		/** @var callable(Contract\Container): T $builder */
		if (is_callable($implementation)) {
			$builder = $implementation;
		} else if (is_object($implementation)) {
			$builder = static fn(): object => $implementation;
		} else {
			$builder = static fn(Contract\Container $container): object => $container->instantiate($implementation);
		}

		$binding = new Binding($builder, $lifetime);

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
		$this->register($contract, $implementation, InstanceLifetime::Singleton, ...$aliases);
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
		$this->register($contract, $implementation, InstanceLifetime::Transient, ...$aliases);
	}

	/**
	 * @template T
	 *
	 * @param class-string<T>|non-empty-string $alias
	 *
	 * @return class-string<T>
	 */
	private function resolveAlias(string $alias): string
	{
		while (!$this->map->has($alias)) {
			if (!$this->aliases->has($alias)) {
				throw new BindingNotFoundException($alias);
			}

			$alias = $this->aliases->get($alias);
		}

		/** @var class-string<T> $alias */
		return $alias;
	}

	/**
	 * @psalm-suppress MoreSpecificImplementedParamType
	 *
	 * @template T
	 *
	 * @param class-string<T>|non-empty-string $id
	 *
	 * @return T
	 */
	public function get(string $id): object
	{
		$id = $this->resolveAlias($id);

		$binding = $this->map->get($id);

		$instance = match ($binding->getLifetime()) {
			InstanceLifetime::Transient => $this->buildTransientInstance($binding),
			InstanceLifetime::Singleton => $this->buildRequestInstance($binding),
		};

		if (!($instance instanceof $id)) {
			throw new InvalidBindingInstanceException($instance, $id);
		}

		return $instance;
	}

	/**
	 * @template T as object
	 *
	 * @param Binding<T> $binding
	 *
	 * @return T
	 */
	private function buildTransientInstance(Binding $binding): object
	{
		$builder = $binding->getBuilder();

		return $builder($this);
	}

	/**
	 * @template T as object
	 *
	 * @param Binding<T> $binding
	 *
	 * @return T
	 */
	private function buildRequestInstance(Binding $binding): object
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
	 * @template T
	 *
	 * @param class-string<T>|non-empty-string $id
	 * @param array $overrideArguments
	 *
	 * @return T
	 * @throws ReflectionException
	 */
	public function instantiate(string $id, array $overrideArguments = []): object
	{
		// check if $id contains a class name
		if (!class_exists($id)) {
			// if not, check if $id is an alias (if not, this will throw)
			$id = $this->resolveAlias($id);
		}
		// $id is a valid class name and can be instantiated
		/** @var class-string<T> $id */

		$reflectionClass = new ReflectionClass($id);
		$constructor = $reflectionClass->getConstructor();
		if ($constructor === null) {
			return $reflectionClass->newInstance();
		}

		$arguments = $this->resolveArguments($constructor, $overrideArguments);

		return $reflectionClass->newInstanceArgs($arguments->asArray());
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param array $overrideArguments
	 * @param InstanceLifetime $lifetime
	 * @param non-empty-string ...$aliases
	 *
	 * @return T
	 */
	public function getOrRegister(string $contract, array $overrideArguments = [], InstanceLifetime $lifetime = InstanceLifetime::Singleton, string ...$aliases): object
	{
		if (!$this->has($contract)) {
			$this->register($contract, null, $lifetime, ...$aliases);
		}

		return $this->get($contract);
	}

	/**
	 * @template T
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
	 * @template T of object
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
				 * @var class-string|null $type
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
		return $reflectionMethod->invokeArgs($object, $arguments->asArray());
	}

	/**
	 * @template T
	 *
	 * @param Closure: T $callback
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
		return $reflectionFunction->invokeArgs($arguments->asArray());
	}

	private function resolveArguments(ReflectionFunctionAbstract $method, array $overrides): ArrayList
	{
		/** @var ArrayList<mixed> $values */
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
		$type = $parameter->getType();
		if ($type === null) {
			throw new MissingTypeHintException($parameter);
		}

		/**
		 * @var class-string $typeName
		 * @psalm-suppress UndefinedMethod
		 */
		$typeName = $type->getName();

		if ($this->has($typeName)) {
			return $this->get($typeName);
		}

		if ($parameter->isDefaultValueAvailable()) {
			return $parameter->getDefaultValue();
		}

		if (!$parameter->allowsNull()) {
			throw new BindingNotFoundException($typeName, $parameter->name);
		}

		return null;

	}

	public function alias(string $alias, string $contract): void
	{
		$this->aliases->put($alias, $contract);
	}
}
