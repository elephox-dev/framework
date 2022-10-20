<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use Elephox\Collection\ArrayList;

/**
 * @psalm-type NonNegativeInteger = int<0,max>
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

	/**
	 * @return ArrayList<TSource>
	 */
	public function toArrayList(): ArrayList;
}
