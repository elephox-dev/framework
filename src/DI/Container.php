<?php

namespace Philly\DI;

use Philly\Collection\HashMap;
use Philly\DI\Contract\ContainerContract;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use RuntimeException;

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
		return $this->map->hasKey($class);
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|callable(ContainerContract): T $implementation
	 * @param InjectionLifetime $lifetime
	 */
	public function register(string $contract, callable|string|object $implementation, InjectionLifetime $lifetime = InjectionLifetime::Request): void
	{
		/** @var callable(Container): T $builder */
		if (is_callable($implementation)) {
			$builder = $implementation;
		} else if (is_object($implementation)) {
			$builder = static fn (): object => $implementation;
		} else {
			$builder = static fn(ContainerContract $container): object => $container->instantiate($implementation);
		}

		$binding = new Binding($contract, $builder, $lifetime);

		$this->map->put($contract, $binding);
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $class
	 *
	 * @return T
	 *
	 * @throws \Philly\DI\NotRegisteredException
	 */
	public function get(string $class): object
	{
		if (!$this->has($class)) {
			throw new NotRegisteredException();
		}

		/** @var Binding<T> $binding */
		$binding = $this->map->get($class);
		if ($binding->getContract() !== $class) {
			throw new RuntimeException("Invalid binding contract for requested class!");
		}

		$builder = $binding->getBuilder();
		switch ($binding->getLifetime()) {
			case InjectionLifetime::Transient:
				return $builder($this);
			case InjectionLifetime::Request:
				$instance = $binding->getInstance();
				if ($instance === null) {
					$instance = $builder($this);

					$binding->setInstance($instance);
				}

				return $instance;
		}

		throw new RuntimeException("Unexpected injection lifetime value.");
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @return T
	 * @throws \ReflectionException
	 * @throws \Philly\DI\NotRegisteredException
	 */
	public function instantiate(string $contract): object
	{
		$reflectionClass = new ReflectionClass($contract);
		$constructor = $reflectionClass->getConstructor();
		if ($constructor === null) {
			return $reflectionClass->newInstance();
		}

		$constructorParameters = $constructor->getParameters();
		$parameters = [];
		foreach ($constructorParameters as $index => $constructorParameter) {
			$parameterType = $constructorParameter->getType();
			if ($parameterType === null) {
				throw new RuntimeException("Missing type parameter in constructor for $contract");
			}

			$requiredTypes = [];
			$optionalTypes = [];
			if ($parameterType instanceof ReflectionNamedType) {
				if ($parameterType->allowsNull()) {
					$optionalTypes[$index] = $parameterType;
				} else {
					$requiredTypes[$index] = $parameterType;
				}
			} else if ($parameterType instanceof ReflectionUnionType) {
				$optionalTypes[$index] = [];
				$requiredTypes[$index] = [];

				foreach ($parameterType->getTypes() as $innerIndex => $parameterTypeOption) {
					if ($parameterTypeOption->allowsNull()) {
						$optionalTypes[$index][$innerIndex] = $parameterTypeOption;
					} else {
						$requiredTypes[$index][$innerIndex] = $parameterTypeOption;
					}
				}
			} else {
				throw new RuntimeException("Unexpected parameter type.");
			}

			foreach ($requiredTypes as $requiredType) {
				if (is_array($requiredType)) {
					foreach ($requiredType as $type) {
						try {
							/** @var class-string $typeName */
							$typeName = $type->getName();

							$instance = $this->get($typeName);
						} catch (NotRegisteredException) {
							continue;
						}

						$parameters[$index] = $instance;
					}

					if (!isset($parameters[$index])) {
						throw new NotRegisteredException("Unable to create instance of $contract: Cannot call constructor.");
					}

					break;
				}

				/** @var class-string $typeName */
				$typeName = $requiredType->getName();

				$parameters[$index] = $this->get($typeName);

				break;
			}

			foreach ($optionalTypes as $optionalType) {
				if (is_array($optionalType)) {
					foreach ($optionalType as $type) {
						try {
							/** @var class-string $typeName */
							$typeName = $type->getName();

							$instance = $this->get($typeName);
						} catch (NotRegisteredException) {
							continue;
						}

						$parameters[$index] = $instance;
					}

					if (!isset($parameters[$index])) {
						$parameters[$index] = null;
					}

					break;
				}

				/** @var class-string $typeName */
				$typeName = $optionalType->getName();

				try {
					$parameters[$index] = $this->get($typeName);
				} catch (NotRegisteredException) {
					$parameters[$index] = null;
				}

				break;
			}
		}

		return $reflectionClass->newInstanceArgs($parameters);
	}
}
