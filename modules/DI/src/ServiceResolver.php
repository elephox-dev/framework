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
	 * @param class-string $className
	 * @param array $overrideArguments
	 *
	 * @return mixed
	 *
	 * @throws ClassNotFoundException
	 * @throws BadMethodCallException
	 */
	public function instantiate(string $className, array $overrideArguments = [], ?Closure $onUnresolved = null): object
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

			$arguments = $this->resolveArguments($constructor, $overrideArguments, $onUnresolved);

			return $reflectionClass->newInstanceArgs($arguments->toList());
		} catch (ReflectionException $e) {
			throw new BadMethodCallException("Failed to instantiate class '$className'", previous: $e);
		}
	}

	/**
	 * @param class-string $className
	 * @param non-empty-string $method
	 * @param array $overrideArguments
	 *
	 * @return mixed
	 *
	 * @throws BadMethodCallException
	 */
	public function call(string $className, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed
	{
		$instance = $this->instantiate($className);

		return $this->callOn($instance, $method, $overrideArguments, $onUnresolved);
	}

	/**
	 * @param object $instance
	 * @param non-empty-string $method
	 * @param array $overrideArguments
	 *
	 * @return mixed
	 *
	 * @throws BadMethodCallException
	 */
	public function callOn(object $instance, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed
	{
		try {
			$reflectionClass = new ReflectionClass($instance);
			$reflectionMethod = $reflectionClass->getMethod($method);
			$arguments = $this->resolveArguments($reflectionMethod, $overrideArguments, $onUnresolved);

			return $reflectionMethod->invokeArgs($instance, $arguments->toList());
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
	 * @param array $overrideArguments
	 *
	 * @return mixed
	 *
	 * @throws BadMethodCallException
	 */
	public function callStatic(string $className, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed
	{
		try {
			$reflectionClass = new ReflectionClass($className);
			$reflectionMethod = $reflectionClass->getMethod($method);
			$arguments = $this->resolveArguments($reflectionMethod, $overrideArguments, $onUnresolved);

			return $reflectionMethod->invokeArgs(null, $arguments->toList());
		} catch (ReflectionException $e) {
			throw new BadMethodCallException("Failed to call method '$method' on class '$className'", previous: $e);
		}
	}

	/**
	 * @param Closure|ReflectionFunction $callback
	 * @param array $overrideArguments
	 *
	 * @return mixed
	 *
	 * @throws BadFunctionCallException
	 */
	public function callback(Closure|ReflectionFunction $callback, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed
	{
		/** @noinspection PhpUnhandledExceptionInspection $callback is never a string */
		$reflectionFunction = $callback instanceof ReflectionFunction ? $callback : new ReflectionFunction($callback);
		$arguments = $this->resolveArguments($reflectionFunction, $overrideArguments, $onUnresolved);

		return $reflectionFunction->invokeArgs($arguments->toList());
	}

	public function resolveArguments(ReflectionFunctionAbstract $function, array $overrideArguments = [], ?Closure $onUnresolved = null): ArrayList
	{
		if (!empty($overrideArguments) && array_is_list($overrideArguments)) {
			return ArrayList::from($overrideArguments);
		}

		/** @var ArrayList<mixed> $arguments */
		$arguments = new ArrayList();
		$parameters = $function->getParameters();

		foreach ($parameters as $parameter) {
			if ($parameter->isVariadic()) {
				$arguments->addAll($overrideArguments);

				break;
			}

			if (array_key_exists($parameter->getName(), $overrideArguments)) {
				/** @var mixed $argument */
				$argument = $overrideArguments[$parameter->getName()];
				unset($overrideArguments[$parameter->getName()]);
			} else {
				/** @var mixed $argument */
				$argument = $this->resolveArgument($arguments->count(), $parameter, $onUnresolved);
			}

			$arguments->add($argument);
		}

		return $arguments;
	}

	private function resolveArgument(int $index, ReflectionParameter $parameter, ?Closure $onUnresolved): mixed
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

			if ($onUnresolved !== null) {
				/** @var mixed */
				return $this->callback($onUnresolved, ['parameter' => $parameter, 'index' => $index]);
			}

			throw new MissingTypeHintException($parameter);
		}

		// TODO: add support for disjunctive normal form types (https://wiki.php.net/rfc/dnf_types)
		if ($type instanceof ReflectionUnionType) {
			$typeNames = array_map(static fn (ReflectionNamedType $t) => $t->getName(), $type->getTypes());
		} else {
			/** @var ReflectionNamedType $type */
			$typeNames = [$type->getName()];
		}

		/** @var list<class-string> $typeNames */
		if ($possibleArgument === null) {
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

		if ($onUnresolved !== null) {
			/** @var mixed */
			return $this->callback($onUnresolved, ['parameter' => $parameter, 'index' => $index]);
		}

		throw new UnresolvedParameterException($parameter->getDeclaringClass()?->getShortName() ?? '<unknown class>', $parameter->getDeclaringFunction()->getShortName(), (string) $type, $parameter->name);
	}
}
