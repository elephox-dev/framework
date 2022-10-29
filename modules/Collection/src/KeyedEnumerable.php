<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Iterator\RangeIterator;
use EmptyIterator;
use InvalidArgumentException;
use Iterator;

/**
 * @template TIteratorKey
 * @template TSource
 *
 * @extends IteratorProvider<TIteratorKey, TSource>
 *
 * @implements GenericKeyedEnumerable<TIteratorKey, TSource>
 */
class KeyedEnumerable extends IteratorProvider implements GenericKeyedEnumerable
{
	/**
	 * @use IsKeyedEnumerable<TIteratorKey, TSource>
	 */
	use IsKeyedEnumerable;

	/**
	 * @template TValue
	 *
	 * @param TValue $value
	 *
	 * @return GenericKeyedEnumerable<mixed, TValue>
	 */
	public static function from(mixed $value): GenericKeyedEnumerable
	{
		if (is_string($value)) {
			$value = str_split($value);
		}

		if (is_array($value)) {
			return new self(new ArrayIterator($value));
		}

		if (is_object($value)) {
			if ($value instanceof Iterator) {
				return new self($value);
			}

			if ($value instanceof GenericKeyedEnumerable) {
				return $value;
			}
		}

		throw new InvalidArgumentException('Value must be iterable');
	}

	/**
	 * @param int $start Inclusive
	 * @param int $end Inclusive
	 * @param int $step
	 *
	 * @return GenericKeyedEnumerable<int, int>
	 */
	public static function range(int $start, int $end, int $step = 1): GenericKeyedEnumerable
	{
		/** @var KeyedEnumerable<int, int> */
		return new self(new RangeIterator($start, $end, $step));
	}

	/**
	 * @return GenericKeyedEnumerable<never, never>
	 */
	public static function empty(): GenericKeyedEnumerable
	{
		/** @var KeyedEnumerable<never, never> */
		return new self(new EmptyIterator());
	}
}
