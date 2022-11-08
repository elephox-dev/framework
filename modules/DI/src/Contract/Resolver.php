<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use BadFunctionCallException;
use BadMethodCallException;
use Closure;
use Elephox\Collection\Contract\GenericList;
use Elephox\DI\ClassNotFoundException;
use ReflectionFunctionAbstract;
use ReflectionParameter;

/**
 * @psalm-type argument-list = array<non-empty-string, mixed>
 */
interface Resolver
{
	/**
	 * @template T
	 *
	 * @param class-string<T> $className
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @return T
	 *
	 * @throws ClassNotFoundException
	 * @throws BadMethodCallException
	 */
	public function instantiate(string $className, array $overrideArguments = [], ?Closure $onUnresolved = null): object;

	/**
	 * @template T as object
	 *
	 * @param class-string<T> $className
	 * @param non-empty-string $method
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @throws BadMethodCallException
	 */
	public function call(string $className, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed;

	/**
	 * @template T as object
	 *
	 * @param class-string<T> $className
	 * @param non-empty-string $method
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @throws BadMethodCallException
	 */
	public function callStatic(string $className, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed;

	/**
	 * @template T
	 *
	 * @param Closure|ReflectionFunctionAbstract $callback
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @return T
	 *
	 * @throws BadFunctionCallException
	 */
	public function callback(Closure|ReflectionFunctionAbstract $callback, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed;

	/**
	 * @param ReflectionFunctionAbstract $function
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 * @return GenericList<mixed>
	 */
	public function resolveArguments(ReflectionFunctionAbstract $function, array $overrideArguments = [], ?Closure $onUnresolved = null): GenericList;
}
