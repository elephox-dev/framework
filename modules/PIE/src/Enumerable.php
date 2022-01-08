<?php
declare(strict_types=1);

namespace Elephox\PIE;

use ArrayIterator;
use Closure;
use EmptyIterator;
use InvalidArgumentException;
use Iterator;
use JetBrains\PhpStorm\Pure;

/**
 * @template TIteratorKey
 * @template TSource
 *
 * @implements GenericEnumerable<TIteratorKey, TSource>
 */
class Enumerable implements GenericEnumerable
{
	/**
	 * @template T
	 *
	 * @param T $value
	 *
	 * @return GenericEnumerable<mixed, T>
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
	 * @return GenericEnumerable<int, int>
	 */
	public static function range(int $start, int $end, int $step = 1): GenericEnumerable
	{
		return new self(new RangeIterator($start, $end, $step));
	}

	/**
	 * @return GenericEnumerable<never, never>
	 */
	public static function empty(): GenericEnumerable
	{
		return new self(new EmptyIterator());
	}

	/**
	 * @uses IsEnumerable<TIteratorKey, TSource>
	 */
	use IsEnumerable;

	/**
	 * @var Iterator<TIteratorKey, TSource>
	 */
	private Iterator $iterator;

	/**
	 * @param Closure(): Iterator<TIteratorKey, TSource>|Iterator<TIteratorKey, TSource> $iterator
	 * @psalm-suppress RedundantConditionGivenDocblockType
	 */
	public function __construct(
		Iterator|Closure $iterator
	) {
		if ($iterator instanceof Iterator) {
			$this->iterator = $iterator;
		} else if (is_callable($iterator)) {
			$result = $iterator();
			if ($result instanceof Iterator) {
				$this->iterator = $result;
			} else {
				throw new InvalidArgumentException('The closure must return an instance of Iterator');
			}
		} else {
			throw new InvalidArgumentException('The first argument must be or return an instance of Iterator');
		}
	}

	/**
	 * @return Iterator<TIteratorKey, TSource>
	 */
	#[Pure] public function getIterator(): Iterator
	{
		return $this->iterator;
	}
}
