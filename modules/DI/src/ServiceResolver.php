<?php
declare(strict_types=1);

namespace Elephox\DI;

use BadFunctionCallException;
use BadMethodCallException;
use Closure;
use Elephox\Collection\ArrayList;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

trait ServiceResolver
{
	abstract protected function getServices(): ServiceCollectionContract;

	/**
	 * @template T
	 *
	 * @param class-string<T> $className
	 * @param array $overrideArguments
	 *
	 * @return T
	 *
	 * @throws ClassNotFoundException
	 * @throws BadMethodCallException
	 */
	public function instantiate(string $className, array $overrideArguments = []): object
	{
		if (!class_exists($className)) {
			throw new ClassNotFoundException($className);
		}

		$reflectionClass = new ReflectionClass($className);
		$constructor = $reflectionClass->getConstructor();

		try {
			if ($constructor === null) {
				return $reflectionClass->newInstance();
			}

			$arguments = $this->resolveArguments($constructor, $overrideArguments);

			return $reflectionClass->newInstanceArgs($arguments->toList());
		} catch (ReflectionException $e) {
			throw new BadMethodCallException("Failed to instantiate class '$className'", previous: $e);
		}
	}

	/**
	 * @template T as object
	 * @template TResult
	 *
	 * @param class-string<T> $className
	 * @param non-empty-string $method
	 * @param array $overrideArguments
	 *
	 * @return TResult
	 *
	 * @throws BadMethodCallException
	 */
	public function call(string $className, string $method, array $overrideArguments = []): mixed
	{
		$instance = $this->instantiate($className);

		try {
			$reflectionClass = new ReflectionClass($instance);
			$reflectionMethod = $reflectionClass->getMethod($method);
			$arguments = $this->resolveArguments($reflectionMethod, $overrideArguments);

			/** @var TResult */
			return $reflectionMethod->invokeArgs($instance, $arguments->toList());
		} catch (ReflectionException $e) {
			throw new BadMethodCallException("Failed to call method '$method' on class '$className'", previous: $e);
		}
	}

	/**
	 * @template T as object
	 * @template TResult
	 *
	 * @param class-string<T> $className
	 * @param non-empty-string $method
	 * @param array $overrideArguments
	 *
	 * @return TResult
	 *
	 * @throws BadMethodCallException
	 */
	public function callStatic(string $className, string $method, array $overrideArguments = []): mixed
	{
		try {
			$reflectionClass = new ReflectionClass($className);
			$reflectionMethod = $reflectionClass->getMethod($method);
			$arguments = $this->resolveArguments($reflectionMethod, $overrideArguments);

			/** @var TResult */
			return $reflectionMethod->invokeArgs(null, $arguments->toList());
		} catch (ReflectionException $e) {
			throw new BadMethodCallException("Failed to call method '$method' on class '$className'", previous: $e);
		}
	}

	/**
	 * @template T
	 *
	 * @param Closure|Closure(mixed): T $callback
	 * @param array $overrideArguments
	 *
	 * @return T
	 *
	 * @throws BadFunctionCallException
	 */
	public function callback(Closure $callback, array $overrideArguments = []): mixed
	{
		try {
			$reflectionFunction = new ReflectionFunction($callback);
			$arguments = $this->resolveArguments($reflectionFunction, $overrideArguments);

			/** @var T */
			return $reflectionFunction->invokeArgs($arguments->toList());
		} catch (ReflectionException $e) {
			throw new BadFunctionCallException('Failed to invoke callback', previous: $e);
		}
	}

	private function resolveArguments(ReflectionFunctionAbstract $method, array $overrides): ArrayList
	{
		/** @var ArrayList<mixed> $values */
		$values = new ArrayList();
		$parameters = $method->getParameters();

		// TODO: implement positional overrides with integer keys in $overrides
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
		/** @var mixed $possibleArgument */
		$possibleArgument = $this->getServices()->get($parameter->getName());
		$type = $parameter->getType();
		if ($type === null) {
			if ($possibleArgument !== null) {
				return $possibleArgument;
			}

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
				try {
					return $this->getServices()->requireService($typeName);
				} catch (ServiceNotFoundException) {
					continue;
				}
			}
		}

		if ($parameter->isDefaultValueAvailable()) {
			return $parameter->getDefaultValue();
		}

		if ($parameter->allowsNull()) {
			return null;
		}

		throw new UnresolvedParameterException($parameter->getDeclaringClass()?->getShortName() ?? '<unknown class>', $parameter->getDeclaringFunction()->getShortName(), (string) $type, $parameter->name);
	}
}
