<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Closure;
use Elephox\DI\InstanceLifetime;
use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
	/**
	 * @psalm-suppress MoreSpecificImplementedParamType
	 *
	 * @param non-empty-string $id
	 * @return bool
	 */
	public function has(string $id): bool;

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|null|callable(Container): T $implementation
	 * @param non-empty-string ...$aliases
	 */
	public function register(string $contract, string|callable|object|null $implementation = null, InstanceLifetime $lifetime = InstanceLifetime::Singleton, string ...$aliases): void;

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param class-string<T>|T|null|callable(Container): T $implementation
	 * @param non-empty-string ...$aliases
	 */
	public function singleton(string $contract, string|callable|object|null $implementation = null, string ...$aliases): void;

	/**
	 * @template T
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
	 * @template T
	 *
	 * @param class-string<T>|non-empty-string $id
	 * @return T
	 */
	public function get(string $id): object;

	/**
	 * @template T
	 *
	 * @param class-string<T> $contract
	 * @param array $overrideArguments
	 *
	 * @return T
	 */
	public function instantiate(string $contract, array $overrideArguments = []): object;

	/**
	 * @template T of object
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
	 * @template T
	 *
	 * @param Closure: T $callback
	 * @param array $overrideArguments
	 * @return T
	 */
	public function callback(Closure $callback, array $overrideArguments = []): mixed;
}
