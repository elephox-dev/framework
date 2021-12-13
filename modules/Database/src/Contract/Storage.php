<?php
declare(strict_types=1);

namespace Elephox\Database\Contract;

interface Storage
{
	public function get(string $entityName, string $key): null|array;

	/**
	 * @param array<string, mixed> $values
	 */
	public function set(string $entityName, string $key, array $values): void;

	/**
	 * @param array<string, mixed> $values
	 */
	public function add(string $entityName, array $values): string;

	public function delete(string $entityName, string $key): void;

	public function exists(string $entityName, string $key): bool;

	/**
	 * @return list<array<string, mixed>>
	 */
	public function all(string $entityName): array;
}
