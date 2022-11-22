<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Iterator\RangeIterator;
use EmptyIterator;
use InvalidArgumentException;
use Traversable;

/**
 * @template TSource
 *
 * @extends IteratorProvider<mixed, TSource>
 *
 * @implements GenericEnumerable<TSource>
 */
class Enumerable extends IteratorProvider implements GenericEnumerable
{
	/**
	 * @use IsEnumerable<TSource>
	 */
	use IsEnumerable;
	/**
	 * @template T
	 *
	 * @param T|iterable<mixed, T> $value
	 *
	 * @return GenericEnumerable<T>
	 */
	public static function from(mixed $value): GenericEnumerable
	{
		if (is_string($value)) {
			$value = str_split($value);
		}

		if (is_array($value)) {
			return new self(new ArrayIterator($value));
		}

		if (is_object($value)) {
			if ($value instanceof GenericEnumerable) {
				return $value;
			}

			if ($value instanceof Traversable) {
				/** @var Traversable<mixed, mixed> $value */
				return new self($value);
			}
		}

		throw new InvalidArgumentException('Value must be iterable');
	}

	/**
	 * @param int $start Inclusive
	 * @param int $end Inclusive
	 * @param int $step
	 *
	 * @return GenericEnumerable<int>
	 */
	public static function range(int $start, int $end, int $step = 1): GenericEnumerable
	{
		/** @var Enumerable<int> */
		return new self(new RangeIterator($start, $end, $step));
	}

	/**
	 * @return GenericEnumerable<never>
	 */
	public static function empty(): GenericEnumerable
	{
		/** @var Enumerable<never> */
		return new self(new EmptyIterator());
	}
}
