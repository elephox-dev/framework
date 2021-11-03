<?php

namespace Philly\DI;

use Philly\Collection\ArrayList;
use Philly\Collection\HashMap;
use Philly\DI\Contract\ContainerContract;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionException;

class Container implements ContainerContract
{
	/** @var \Philly\Collection\HashMap<class-string, Binding> */
	private HashMap $map;

	public function __construct()
	{
		$this->map = new HashMap();
	}

	public function has(string $class): bool
	{
		return $this->map->has($class);
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|callable(ContainerContract): T $implementation
	 * @param BindingLifetime $lifetime
	 */
	public function register(string $contract, callable|string|object $implementation, BindingLifetime $lifetime = BindingLifetime::Request): void
	{
		/** @var callable(Container): T $builder */
		if (is_callable($implementation)) {
			$builder = $implementation;
		} else if (is_object($implementation)) {
			$builder = static fn(): object => $implementation;
		} else {
			$builder = static fn(ContainerContract $container): object => $container->instantiate($implementation);
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
		$builder = $binding->getBuilder();
		switch ($binding->getLifetime()) {
			case BindingLifetime::Transient:
				$instance = $builder($this);

				if (!($instance instanceof $class)) {
					throw new InvalidBindingInstanceException($instance, $class);
				}

				return $instance;
			case BindingLifetime::Request:
				$instance = $binding->getInstance();
				if ($instance === null) {
					$instance = $builder($this);

					$binding->setInstance($instance);
				}

				if (!($instance instanceof $class)) {
					throw new InvalidBindingInstanceException($instance, $class);
				}

				return $instance;
		}

		throw new BindingException("Unexpected injection lifetime value.");
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @return T
	 * @throws ReflectionException
	 */
	public function instantiate(string $contract): object
	{
		$reflectionClass = new ReflectionClass($contract);
		$constructor = $reflectionClass->getConstructor();
		if ($constructor === null) {
			return $reflectionClass->newInstance();
		}

		$parameters = $this->resolveParameterValues($constructor);

		return $reflectionClass->newInstanceArgs($parameters->asArray());
	}

	private function resolveParameterValues(ReflectionMethod $method): ArrayList
	{
		/** @var ArrayList<object|null> $values */
		$values = new ArrayList();
		$parameters = $method->getParameters();

		foreach ($parameters as $parameter) {
			$values[] = $this->resolveParameterValue($parameter);
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
			if (!$parameter->allowsNull()) {
				throw new BindingNotFoundException($typeName);
			}

			return null;
		}

		return $this->get($typeName);
	}
}
