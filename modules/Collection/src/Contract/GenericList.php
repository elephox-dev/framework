<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use ArrayAccess;

/**
 * @template T
 *
 * @extends GenericKeyedEnumerable<int, T>
 * @extends ArrayAccess<int, T>
 */
interface GenericList extends GenericKeyedEnumerable, ArrayAccess
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
	public function remove(mixed $value, ?callable $comparer = null): bool;

	/**
	 * @param T $value
	 */
	public function put(int $index, mixed $value): bool;

	/**
	 * @return T
	 */
	public function elementAt(int $index): mixed;

	/**
	 * @return T
	 */
	public function removeAt(int $index): mixed;

	/**
	 * @param T $value
	 * @param null|callable(T, T): bool $comparer
	 */
	public function indexOf(mixed $value, ?callable $comparer = null): ?int;

	/**
	 * @param T $value
	 * @param null|callable(T, T): bool $comparer
	 */
	public function lastIndexOf(mixed $value, ?callable $comparer = null): ?int;

	public function isEmpty(): bool;
}
