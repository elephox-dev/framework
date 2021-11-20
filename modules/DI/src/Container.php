<?php
declare(strict_types=1);

namespace Elephox\DI;

use Closure;
use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use JetBrains\PhpStorm\Pure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionProperty;

class Container implements Contract\Container
{
	/** @var \Elephox\Collection\ArrayMap<class-string, Binding> */
	private ArrayMap $map;

	public function __construct()
	{
		$this->map = new ArrayMap();

		$this->register(Contract\Container::class, $this);
		$this->register(__CLASS__, $this);
	}

	#[Pure] public function has(string $class): bool
	{
		return $this->map->has($class);
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|callable(Contract\Container): T $implementation
	 * @param BindingLifetime $lifetime
	 */
	public function register(string $contract, callable|string|object $implementation, BindingLifetime $lifetime = BindingLifetime::Request): void
	{
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
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $class
	 *
	 * @return T
	 */
	public function get(string $class): object
	{
		if (!$this->has($class)) {
			throw new BindingNotFoundException($class);
		}

		/** @var Binding<T> $binding */
		$binding = $this->map->get($class);

		$instance = match ($binding->getLifetime()) {
			BindingLifetime::Transient => $this->buildTransientInstance($binding),
			BindingLifetime::Request => $this->buildRequestInstance($binding),
		};

		if (!($instance instanceof $class)) {
			throw new InvalidBindingInstanceException($instance, $class);
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
	 * @param class-string<T> $contract
	 * @param array<array-key, object|null> $arguments
	 *
	 * @return T
	 * @throws ReflectionException
	 */
	public function instantiate(string $contract, array $arguments = []): object
	{
		$reflectionClass = new ReflectionClass($contract);
		$constructor = $reflectionClass->getConstructor();
		if ($constructor === null) {
			return $reflectionClass->newInstance();
		}

		$parameters = $this->resolveParameterValues($constructor, $arguments);

		return $reflectionClass->newInstanceArgs($parameters->asArray());
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
	 * @param class-string<T>|T $implementation
	 * @param array<array-key, object|null> $arguments
	 *
	 * @return TResult
	 * @throws ReflectionException
	 */
	public function call(string|object $implementation, string $method, array $arguments = []): mixed
	{
		/** @var T $object */
		if (is_string($implementation)) {
			$object = $this->get($implementation);
		} else {
			/** @var T $implementation */
			$object = $implementation;
		}

		$reflectionClass = new ReflectionClass($object);
		$reflectionMethod = $reflectionClass->getMethod($method);
		$parameters = $this->resolveParameterValues($reflectionMethod, $arguments);

		/** @var TResult */
		return $reflectionMethod->invokeArgs($object, $parameters->asArray());
	}

	/**
	 * @template T
	 *
	 * @param callable(): T $callback
	 * @param array<array-key, object|null> $arguments
	 *
	 * @return T
	 * @throws ReflectionException
	 */
	public function callback(callable $callback, array $arguments = []): mixed
	{
		$reflectionFunction = new ReflectionFunction(Closure::fromCallable($callback));
		$parameters = $this->resolveParameterValues($reflectionFunction, $arguments);
		/** @var T */
		return $reflectionFunction->invokeArgs($parameters->asArray());
	}

	/**
	 * @param array<array-key, object|null> $given
	 */
	private function resolveParameterValues(ReflectionFunctionAbstract $method, array $given): ArrayList
	{
		/** @var ArrayList<object|null> $values */
		$values = new ArrayList();
		$parameters = $method->getParameters();

		foreach ($parameters as $i => $parameter) {
			if ($parameter->isVariadic()) {
				$values->addAll(...array_slice($given, $i));
				break;
			}

			if (array_key_exists($parameter->getName(), $given)) {
				$values->add($given[$parameter->getName()]);
			} else {
				$values->add($this->resolveParameterValue($parameter));
			}
		}

		return $values;
	}

	private function resolveParameterValue(ReflectionParameter $parameter): ?object
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

		if (!$this->has($typeName)) {
			if ($parameter->isDefaultValueAvailable()) {
				/** @var object|null */
				return $parameter->getDefaultValue();
			}

			if (!$parameter->allowsNull()) {
				throw new BindingNotFoundException($typeName);
			}

			return null;
		}

		return $this->get($typeName);
	}
}
