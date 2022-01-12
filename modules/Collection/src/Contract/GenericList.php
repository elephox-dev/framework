<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use ArrayAccess;

/**
 * @template T
 *
 * @extends GenericKeyedEnumerable<int, T>
 */
interface GenericList extends GenericKeyedEnumerable, ArrayAccess
{
	/**
	 * @param T $value
	 */
	public function add(mixed $value): bool;

	/**
	 * @param iterable<T> $values
	 *
	 * @return bool
	 */
	public function addAll(iterable $values): bool;

	/**
	 * @param T $value
	 * @param null|callable(T, T): bool $comparer
	 *
	 * @return bool
	 */
	public function remove(mixed $value, ?callable $comparer = null): bool;

	/**
	 * @param int $index
	 * @param T $value
	 * @return bool
	 */
	public function put(int $index, mixed $value): bool;

	/**
	 * @param int $index
	 * @return T
	 */
	public function elementAt(int $index): mixed;

	/**
	 * @param int $index
	 * @return T
	 */
	public function removeAt(int $index): mixed;

	/**
	 * @param T $value
	 * @param null|callable(T, T): bool $comparer
	 * @return int|null
	 */
	public function indexOf(mixed $value, ?callable $comparer = null): ?int;

	/**
	 * @param T $value
	 * @param null|callable(T, T): bool $comparer
	 * @return int|null
	 */
	public function lastIndexOf(mixed $value, ?callable $comparer = null): ?int;

	public function isEmpty(): bool;
}
