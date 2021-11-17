<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Elephox\DI\BindingLifetime;

interface Container
{
	/**
	 * @param class-string $class
	 * @return bool
	 */
	public function has(string $class): bool;

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|callable(Container): T $implementation
	 */
	public function register(string $contract, string|callable|object $implementation, BindingLifetime $lifetime = BindingLifetime::Request): void;

	/**
	 * @template T
	 *
	 * @param class-string<T> $class
	 * @return T
	 */
	public function get(string $class): object;

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param array<array-key, object|null> $arguments
	 *
	 * @return T
	 */
	public function instantiate(string $contract, array $arguments = []): object;

	/**
	 * @template T as object
	 * @template TResult
	 *
	 * @param class-string<T>|T $implementation
	 * @param string $method
	 * @param array<array-key, object|null> $arguments
	 *
	 * @return TResult
	 */
	public function call(string|object $implementation, string $method, array $arguments = []): mixed;

	/**
	 * @template T
	 *
	 * @param callable(): T $callback
	 * @param array<array-key, object|null> $arguments
	 * @return T
	 */
	public function callback(callable $callback, array $arguments = []): mixed;
}