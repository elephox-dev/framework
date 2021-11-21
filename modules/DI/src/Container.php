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

	/** @var \Elephox\Collection\ArrayMap<non-empty-string, class-string> */
	private ArrayMap $aliases;

	public function __construct()
	{
		$this->map = new ArrayMap();
		$this->aliases = new ArrayMap();

		$this->register(Contract\Container::class, $this);
		$this->register(__CLASS__, $this);
	}

	#[Pure] public function has(string $name): bool
	{
		return $this->map->has($name) || $this->aliases->has($name);
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|null|callable(Contract\Container): T $implementation
	 * @param BindingLifetime $lifetime
	 * @param non-empty-string ...$aliases
	 */
	public function register(string $contract, callable|string|object|null $implementation = null, BindingLifetime $lifetime = BindingLifetime::Request, string ...$aliases): void
	{
		if ($implementation === null) {
			if (!class_exists($contract)) {
				throw new InvalidArgumentException("Class $contract does not exist");
			}

			self::register($contract, $contract);

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
	 * @param class-string<T>|non-empty-string $name
	 *
	 * @return T
	 */
	public function get(string $name): object
	{
		if (!$this->map->has($name)) {
			if (!$this->aliases->has($name)) {
				throw new BindingNotFoundException($name);
			}

			$name = $this->aliases->get($name);
		}

		$binding = $this->map->get($name);

		$instance = match ($binding->getLifetime()) {
			BindingLifetime::Transient => $this->buildTransientInstance($binding),
			BindingLifetime::Request => $this->buildRequestInstance($binding),
		};

		if (!($instance instanceof $name)) {
			throw new InvalidBindingInstanceException($instance, $name);
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
	 * @param class-string<T>|non-empty-string $contract
	 * @param array $overrideArguments
	 *
	 * @return T
	 * @throws ReflectionException
	 */
	public function instantiate(string $contract, array $overrideArguments = []): object
	{
		if ($this->aliases->has($contract)) {
			$contract = $this->aliases->get($contract);
		}
		/** @var class-string<T> $contract */

		$reflectionClass = new ReflectionClass($contract);
		$constructor = $reflectionClass->getConstructor();
		if ($constructor === null) {
			return $reflectionClass->newInstance();
		}

		$arguments = $this->resolveArguments($constructor, $overrideArguments);

		return $reflectionClass->newInstanceArgs($arguments->asArray());
	}

	/**
	 * @template T of object
	 *
	 * @param class-string<T>|T|non-empty-string $implementation
	 * @param array $properties
	 *
	 * @return T
	 * @throws ReflectionException
	 */
	public function restore(object|string $implementation, array $properties = []): object
	{
		/** @var T|non-empty-string $implementation */
		if (is_string($implementation) && $this->aliases->has($implementation)) {
			$implementation = $this->aliases->get($implementation);
		}
		/** @var T|class-string<T> $implementation */

		$reflectionClass = new ReflectionClass($implementation);
		$instance = $reflectionClass->newInstanceWithoutConstructor();
		$classProperties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
		$defaultPropertyValues = $reflectionClass->getDefaultProperties();

		foreach ($classProperties as $classProperty) {
			$classProperty->setAccessible(true);
			$classProperty->setValue($instance, $properties[$classProperty->getName()] ?? $defaultPropertyValues[$classProperty->getName()]);
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
	 * @param callable(): T $callback
	 * @param array $overrideArguments
	 *
	 * @return T
	 * @throws ReflectionException
	 */
	public function callback(callable $callback, array $overrideArguments = []): mixed
	{
		$reflectionFunction = new ReflectionFunction(Closure::fromCallable($callback));
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
				$values->addAll(...array_slice($overrides, $usedOverrides));
				break;
			}

			if (array_key_exists($parameter->getName(), $overrides)) {
				$usedOverrides++;
				$values->add($overrides[$parameter->getName()]);
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
			throw new BindingNotFoundException($typeName);
		}

		return null;

	}

	public function alias(string $alias, string $contract): void
	{
		$this->aliases->put($alias, $contract);
	}
}
