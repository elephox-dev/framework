<?php
declare(strict_types=1);

namespace Elephox\DI;

use BadFunctionCallException;
use BadMethodCallException;
use Closure;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

trait ServiceResolver
{
	abstract protected function getServices(): ServiceCollectionContract;

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

			/** @var object */
			return $reflectionClass->newInstanceArgs($arguments->toList());
		} catch (ReflectionException $e) {
			throw new BadMethodCallException("Failed to instantiate class '$className'", previous: $e);
		}
	}

	/**
	 * @param class-string $className
	 * @param non-empty-string $method
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
	 * @param non-empty-string $method
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

	/**
	 * @return ArrayList<mixed>
	 */
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

		if ($type instanceof ReflectionUnionType) {
			$extractTypeNames = static function (ReflectionUnionType|ReflectionIntersectionType $refType, callable $self): GenericEnumerable {
				return collect(...$refType->getTypes())
					->select(static function (mixed $t) use ($self): array {
						assert($t instanceof ReflectionType, '$t must be an instance of ReflectionType');

						/** @var Closure(ReflectionUnionType|ReflectionIntersectionType, Closure): GenericEnumerable<class-string> $self */
						if ($t instanceof ReflectionUnionType) {
							return $self($t, $self)->toList();
						}

						if ($t instanceof ReflectionIntersectionType) {
							return [$self($t, $self)->toList()];
						}

						if ($t instanceof ReflectionNamedType) {
							return [$t->getName()];
						}

						throw new ReflectionException('Unsupported ReflectionType: ' . get_debug_type($t));
					});
			};

			/** @psalm-suppress DocblockTypeContradiction */
			$typeNames = $extractTypeNames($type, $extractTypeNames)->select(static fn (string|array $t): string|array => is_array($t) ? collect(...$t)->flatten()->toList() : $t);
		} else {
			/** @var ReflectionNamedType $type */
			$typeNames = [$type->getName()];
		}

		/** @var list<class-string|list<class-string>> $typeNames */
		if ($possibleArgument === null) {
			foreach ($typeNames as $typeName) {
				try {
					if (is_string($typeName)) {
						return $this->getServices()->requireService($typeName);
					}

					if (is_array($typeName)) {
						/** @var class-string $combinedTypeName */
						$combinedTypeName = implode('&', $typeName);

						return $this->getServices()->requireService($combinedTypeName);
					}
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
