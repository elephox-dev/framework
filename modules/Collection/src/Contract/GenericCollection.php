<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TSource
 */
interface GenericCollection
{
	/**
	 * @param NonNegativeInteger $size
	 *
	 * @return GenericEnumerable<non-empty-list<TSource>>
	 */
	public function chunk(int $size): GenericEnumerable;

	/**
	 * @param TSource $value
	 * @param null|callable(TSource, TSource): bool $comparer
	 */
	public function contains(mixed $value, ?callable $comparer = null): bool;

	public function isEmpty(): bool;

	/**
	 * @return list<TSource>
	 */
	public function toList(): array;
}
