<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use BadFunctionCallException;
use BadMethodCallException;
use Closure;
use Elephox\DI\ClassNotFoundException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionParameter;

/**
 * @psalm-type argument-list = array<non-empty-string, mixed>
 */
interface Resolver
{
	/**
	 * @template TClass of object
	 *
	 * @param class-string<TClass> $className
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): (TClass|null) $onUnresolved
	 *
	 * @return TClass
	 *
	 * @throws ClassNotFoundException
	 * @throws BadMethodCallException
	 */
	public function instantiate(string $className, array $overrideArguments = [], ?Closure $onUnresolved = null): object;

	/**
	 * @param class-string $className
	 * @param non-empty-string $method
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @throws BadMethodCallException
	 */
	public function callMethod(string $className, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed;

	/**
	 * @param object $instance
	 * @param non-empty-string $method
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @return mixed
	 *
	 * @throws BadMethodCallException
	 */
	public function callMethodOn(object $instance, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed;

	/**
	 * @param class-string $className
	 * @param non-empty-string $method
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @return mixed
	 *
	 * @throws BadMethodCallException
	 */
	public function callStaticMethod(string $className, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed;

	/**
	 * @template TResult
	 *
	 * @param ReflectionFunction|Closure|Closure(mixed): TResult $callback
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): (null|TResult) $onUnresolved
	 *
	 * @return TResult
	 *
	 * @throws BadFunctionCallException
	 */
	public function call(Closure|ReflectionFunction $callback, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed;

	/**
	 * @param ReflectionFunctionAbstract $function
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @return iterable<int, mixed>
	 */
	public function resolveArguments(ReflectionFunctionAbstract $function, array $overrideArguments = [], ?Closure $onUnresolved = null): iterable;
}
