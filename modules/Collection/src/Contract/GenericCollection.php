<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use Elephox\Support\Contract\DeepCloneable;

/**
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TSource
 */
interface GenericCollection extends DeepCloneable
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
	 *
	 * @return bool
	 */
	public function contains(mixed $value, ?callable $comparer = null): bool;

	/**
	 * @return list<TSource>
	 */
	public function toList(): array;
}
