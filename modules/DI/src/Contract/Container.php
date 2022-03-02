<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Closure;
use Elephox\DI\InstanceLifetime;
use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface, NotContainerSerializable
{
	/**
	 * @param string $id
	 * @return bool
	 */
	public function has(string $id): bool;

	/**
	 * @template T as object
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|null|callable(Container): T $implementation
	 * @param non-empty-string ...$aliases
	 */
	public function register(string $contract, string|callable|object|null $implementation = null, InstanceLifetime $lifetime = InstanceLifetime::Singleton, string ...$aliases): void;

	/**
	 * @template T as object
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|null|callable(Container): T $implementation
	 * @param non-empty-string ...$aliases
	 */
	public function singleton(string $contract, string|callable|object|null $implementation = null, string ...$aliases): void;

	/**
	 * @template T as object
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|null|callable(Container): T $implementation
	 * @param non-empty-string ...$aliases
	 */
	public function transient(string $contract, string|callable|object|null $implementation = null, string ...$aliases): void;

	/**
	 * @param non-empty-string $alias
	 * @param non-empty-string $contract
	 */
	public function alias(string $alias, string $contract): void;

	/**
	 * @psalm-suppress MoreSpecificImplementedParamType
	 *
	 * @template T as object
	 *
	 * @param class-string<T>|non-empty-string $id
	 *
	 * @return T
	 */
	public function get(string $id): object;

	/**
	 * @template T as object
	 *
	 * @param class-string<T>|non-empty-string $id
	 * @param array $overrideArguments
	 *
	 * @return T
	 */
	public function instantiate(string $id, array $overrideArguments = []): object;

	/**
	 * @template T as object
	 *
	 * @param class-string<T>|non-empty-string $contract
	 * @param class-string<T>|T|null|callable(Container): T $implementation
	 * @param array $overrideArguments
	 * @param InstanceLifetime $lifetime
	 * @param non-empty-string ...$aliases
	 *
	 * @return T
	 */
	public function getOrRegister(string $contract, callable|string|object|null $implementation = null, array $overrideArguments = [], InstanceLifetime $lifetime = InstanceLifetime::Singleton, string ...$aliases): object;

	/**
	 * @template T as object
	 *
	 * @param class-string<T>|non-empty-string $id
	 * @param array $overrideArguments
	 *
	 * @return T
	 */
	public function getOrInstantiate(string $id, array $overrideArguments = []): object;

	/**
	 * @template T as object
	 * @template TReturn
	 *
	 * @param class-string<T> $class
	 * @param callable(T): TReturn $callback
	 * @return TReturn
	 */
	public function tap(string $class, callable $callback): mixed;

	/**
	 * @template T as object
	 * @template TReturn
	 *
	 * @param class-string<T> $class
	 * @param callable(T): TReturn $callback
	 * @param TReturn|null $fallback
	 * @return TReturn|null
	 */
	public function tapOptional(string $class, callable $callback, mixed $fallback = null): mixed;

	/**
	 * @template T as object
	 *
	 * @param class-string<T>|T $implementation
	 * @param array $properties
	 *
	 * @return T
	 */
	public function restore(string|object $implementation, array $properties = []): object;

	/**
	 * @template T as object
	 * @template TResult
	 *
	 * @param class-string<T>|non-empty-string|T $implementation
	 * @param string $method
	 * @param array $overrideArguments
	 *
	 * @return TResult
	 */
	public function call(string|object $implementation, string $method, array $overrideArguments = []): mixed;

	/**
	 * @template T as object
	 *
	 * @param Closure(): T $callback
	 * @param array $overrideArguments
	 * @return T
	 */
	public function callback(Closure $callback, array $overrideArguments = []): mixed;
}
