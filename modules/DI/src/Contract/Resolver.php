<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use BadFunctionCallException;
use BadMethodCallException;
use Closure;
use Elephox\Collection\Contract\GenericList;
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
	 * @param class-string $className
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @return object
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
	public function call(string $className, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed;

	/**
	 * @param object $instance
	 * @param non-empty-string $method
	 * @param argument-list $overrideArguments
	 * @param Closure|null $onUnresolved
	 *
	 * @return mixed
	 *
	 * @throws BadMethodCallException
	 */
	public function callOn(object $instance, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed;

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
	public function callStatic(string $className, string $method, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed;

	/**
	 * @param Closure|ReflectionFunction $callback
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @return mixed
	 *
	 * @throws BadFunctionCallException
	 */
	public function callback(Closure|ReflectionFunction $callback, array $overrideArguments = [], ?Closure $onUnresolved = null): mixed;

	/**
	 * @param ReflectionFunctionAbstract $function
	 * @param argument-list $overrideArguments
	 * @param null|Closure(ReflectionParameter $param, int $index): mixed $onUnresolved
	 *
	 * @return GenericList<mixed>
	 */
	public function resolveArguments(ReflectionFunctionAbstract $function, array $overrideArguments = [], ?Closure $onUnresolved = null): GenericList;
}
