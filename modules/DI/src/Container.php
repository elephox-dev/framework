<?php
declare(strict_types=1);

namespace Philly\DI;

use JetBrains\PhpStorm\Pure;
use Philly\Collection\ArrayList;
use Philly\Collection\ArrayMap;
use Philly\DI\Contract\ContainerContract;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionException;

class Container implements ContainerContract
{
	/** @var \Philly\Collection\ArrayMap<class-string, Binding> */
	private ArrayMap $map;

	public function __construct()
	{
		$this->map = new ArrayMap();
	}

	#[Pure] public function has(string $class): bool
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
