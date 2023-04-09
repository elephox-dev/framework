<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template T
 *
 * @extends GenericKeyedEnumerable<int, T>
 */
interface GenericReadonlyList extends GenericKeyedEnumerable
{
	/**
	 * @return T
	 *
	 * @param int $index
	 */
	public function elementAt(int $index): mixed;

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

	/**
	 * @return GenericList<T>&static
	 */
	public function slice(int $offset, ?int $length = null): static;
}
