<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Closure;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Iterator\RangeIterator;
use EmptyIterator;
use InvalidArgumentException;
use Iterator;
use JetBrains\PhpStorm\Pure;

/**
 * @template TSource
 *
 * @implements GenericEnumerable<TSource>
 */
class Enumerable implements GenericEnumerable
{
	/**
	 * @template T
	 *
	 * @param T $value
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
			if ($value instanceof Iterator) {
				return new self($value);
			}

			if ($value instanceof GenericEnumerable) {
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
	 * @return GenericEnumerable<int>
	 */
	public static function range(int $start, int $end, int $step = 1): GenericEnumerable
	{
		return new self(new RangeIterator($start, $end, $step));
	}

	/**
	 * @return GenericEnumerable<never>
	 */
	public static function empty(): GenericEnumerable
	{
		return new self(new EmptyIterator());
	}

	/**
	 * @use IsEnumerable<TSource>
	 */
	use IsEnumerable;

	/**
	 * @var Iterator<mixed, TSource>
	 */
	private Iterator $iterator;

	/**
	 * @param Iterator<mixed, TSource>|Closure(): Iterator<mixed, TSource> $iterator
	 * @psalm-suppress RedundantConditionGivenDocblockType
	 */
	public function __construct(
		Iterator|Closure $iterator,
	) {
		if ($iterator instanceof Iterator) {
			$this->iterator = $iterator;
		} else {
			$result = $iterator();
			if ($result instanceof Iterator) {
				$this->iterator = $result;
			} else {
				throw new InvalidArgumentException('The closure must return an instance of Iterator');
			}
		}
	}

	/**
	 * @return Iterator<mixed, TSource>
	 */
	#[Pure]
	public function getIterator(): Iterator
	{
		return $this->iterator;
	}
}
