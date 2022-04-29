<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use BadFunctionCallException;
use BadMethodCallException;
use Closure;
use Elephox\DI\ClassNotFoundException;

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
	 *
	 * @return T
	 *
	 * @throws ClassNotFoundException
	 * @throws BadMethodCallException
	 */
	public function instantiate(string $className, array $overrideArguments = []): object;

	/**
	 * @template T as object
	 *
	 * @param class-string<T> $className
	 * @param non-empty-string $method
	 * @param argument-list $overrideArguments
	 *
	 * @throws BadMethodCallException
	 */
	public function call(string $className, string $method, array $overrideArguments = []): mixed;

	/**
	 * @template T as object
	 *
	 * @param class-string<T> $className
	 * @param non-empty-string $method
	 * @param argument-list $overrideArguments
	 *
	 * @throws BadMethodCallException
	 */
	public function callStatic(string $className, string $method, array $overrideArguments = []): mixed;

	/**
	 * @template T
	 *
	 * @param Closure|Closure(mixed): T $callback
	 * @param argument-list $overrideArguments
	 *
	 * @return T
	 *
	 * @throws BadFunctionCallException
	 */
	public function callback(Closure $callback, array $overrideArguments = []): mixed;
}
