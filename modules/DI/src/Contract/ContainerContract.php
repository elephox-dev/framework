<?php
declare(strict_types=1);

namespace Elephox\DI\Contract;

use Elephox\DI\BindingLifetime;

interface ContainerContract
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
	 * @param class-string<T>|T|callable(ContainerContract): T $implementation
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
	 * @return T
	 */
	public function instantiate(string $contract): object;
}
