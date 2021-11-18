<?php
declare(strict_types=1);

namespace Elephox\Database\Contract;

/**
 * @template T
 */
interface Storage
{
	/**
	 * @param string $key
	 * @return array<string, mixed>
	 */
	public function get(string $key): array;

	/**
	 * @param string $key
	 * @param array<string, mixed> $values
	 */
	public function set(string $key, array $values): void;

	/**
	 * @param array<string, mixed> $values
	 */
	public function add(array $values): string;

	public function delete(string $key): void;

	public function exists(string $key): bool;

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function all(): array;
}
