<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Closure;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Iterator\RangeIterator;
use Elephox\Support\DeepCloneable;
use EmptyIterator;
use InvalidArgumentException;
use Iterator;
use JetBrains\PhpStorm\Pure;

/**
 * @template TIteratorKey
 * @template TSource
 *
 * @implements GenericKeyedEnumerable<TIteratorKey, TSource>
 */
class KeyedEnumerable implements GenericKeyedEnumerable
{
	/**
	 * @template T
	 *
	 * @param T $value
	 *
	 * @return GenericKeyedEnumerable<mixed, T>
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
		return new self(new RangeIterator($start, $end, $step));
	}

	/**
	 * @return GenericKeyedEnumerable<never, never>
	 */
	public static function empty(): GenericKeyedEnumerable
	{
		return new self(new EmptyIterator());
	}

	/**
	 * @uses IsKeyedEnumerable<TIteratorKey, TSource>
	 */
	use IsKeyedEnumerable, DeepCloneable;

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
