<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use ArrayAccess;

/**
 * @template T
 *
 * @extends GenericReadonlyList<T>
 * @extends ArrayAccess<int, T>
 */
interface GenericList extends GenericReadonlyList, ArrayAccess
{
	/**
	 * @param T $value
	 */
	public function add(mixed $value): bool;

	/**
	 * @param iterable<mixed, T> $values
	 */
	public function addAll(iterable $values): bool;

	/**
	 * @param T $value
	 * @param null|callable(T, T): bool $comparer
	 */
	public function removeValue(mixed $value, ?callable $comparer = null): bool;

	/**
	 * @param T $value
	 * @param int $index
	 */
	public function put(int $index, mixed $value): bool;

	/**
	 * @param int $index
	 * @param T $value
	 */
	public function insertAt(int $index, mixed $value): void;

	/**
	 * @return T
	 *
	 * @param int $index
	 */
	public function removeAt(int $index): mixed;

	public function clear(): void;
}
