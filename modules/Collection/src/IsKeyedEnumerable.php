<?php
declare(strict_types=1);

namespace Elephox\Collection;

use AppendIterator;
use CachingIterator;
use CallbackFilterIterator;
use Countable;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Contract\GenericGroupedKeyedEnumerable;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Contract\GenericOrderedEnumerable;
use Elephox\Collection\Contract\GenericKeyValuePair;
use Elephox\Collection\Iterator\FlipIterator;
use Elephox\Collection\Iterator\GroupingIterator;
use Elephox\Collection\Iterator\KeySelectIterator;
use Elephox\Collection\Iterator\OrderedIterator;
use Elephox\Collection\Iterator\ReverseIterator;
use Elephox\Collection\Iterator\SelectIterator;
use Elephox\Collection\Iterator\UniqueByIterator;
use Elephox\Collection\Iterator\WhileIterator;
use EmptyIterator;
use InvalidArgumentException;
use Iterator;
use LimitIterator;
use MultipleIterator as ParallelIterator;
use NoRewindIterator;
use OutOfBoundsException;
use Stringable;

/**
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TIteratorKey
 * @template TSource
 */
trait IsKeyedEnumerable
{
	// TODO: rewrite more functions to use iterators

	/**
	 * @return Iterator<TIteratorKey, TSource>
	 */
	abstract public function getIterator(): Iterator;

	/**
	 * @template TAccumulate
	 *
	 * @param callable(TAccumulate, TSource, TIteratorKey): TAccumulate $accumulator
	 * @param TAccumulate $seed
	 *
	 * @return TAccumulate
	 */
	public function aggregate(callable $accumulator, mixed $seed = null): mixed
	{
		$result = $seed;

		/**
		 * @var TIteratorKey $elementKey
		 */
		foreach ($this->getIterator() as $elementKey => $element) {
			$result = $accumulator($result, $element, $elementKey);
		}

		return $result;
	}

	public function all(callable $predicate): bool
	{
		foreach ($this->getIterator() as $elementKey => $element) {
			if (!$predicate($element, $elementKey)) {
				return false;
			}
		}

		return true;
	}

	public function any(?callable $predicate = null): bool
	{
		foreach ($this->getIterator() as $elementKey => $element) {
			if ($predicate === null || $predicate($element, $elementKey)) {
				return true;
			}
		}

		return false;
	}

	public function append(mixed $key, mixed $value): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(function () use ($value, $key) {
			yield from $this->getIterator();

			yield $key => $value;
		});
	}

	public function appendAll(iterable $values): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(function () use ($values) {
			yield from $this->getIterator();
			yield from $values;
		});
	}

	/**
	 * @param callable(TSource, TIteratorKey): numeric $selector
	 *
	 * @return numeric
	 */
	public function average(callable $selector): int|float|string
	{
		$sum = null;
		$count = 0;

		/**
		 * @var TIteratorKey $elementKey
		 */
		foreach ($this->getIterator() as $elementKey => $element) {
			$value = $selector($element, $elementKey);

			/** @var null|numeric $sum */
			if ($sum === null) {
				$sum = $value;
			} else {
				$sum += $value;
			}

			$count++;
		}

		if ($count === 0) {
			throw new EmptySequenceException();
		}

		/** @var numeric $sum */
		return $sum / $count;
	}

	/**
	 * @param NonNegativeInteger $size
	 *
	 * @return GenericEnumerable<non-empty-list<TSource>>
	 */
	public function chunk(int $size): GenericEnumerable
	{
		if ($size <= 0) {
			throw new InvalidArgumentException('Chunk size must be greater than zero.');
		}

		/** @var GenericEnumerable<non-empty-list<TSource>> */
		return new Enumerable(function () use ($size) {
			$chunk = [];
			/** @var TSource $element */
			foreach ($this->getIterator() as $element) {
				if (count($chunk) === $size) {
					yield $chunk;

					$chunk = [$element];
				} else {
					$chunk[] = $element;
				}
			}

			if ($chunk) {
				yield $chunk;
			}
		});
	}

	public function concat(GenericKeyedEnumerable ...$other): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(function () use ($other) {
			yield from $this;

			foreach ($other as $enumerable) {
				yield from $enumerable;
			}
		});
	}

	public function contains(mixed $value, ?callable $comparer = null): bool
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		foreach ($this->getIterator() as $element) {
			if ($comparer($value, $element)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param null|callable(TSource, TIteratorKey, Iterator<TIteratorKey, TSource>): bool $predicate
	 *
	 * @return NonNegativeInteger
	 */
	public function count(?callable $predicate = null): int
	{
		if ($predicate === null) {
			$iterator = $this->getIterator();

			if ($iterator instanceof Countable) {
				/** @var NonNegativeInteger */
				return $iterator->count();
			}
		} else {
			$iterator = new CallbackFilterIterator($this->getIterator(), $predicate);
		}

		return iterator_count($iterator);
	}

	/**
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function distinct(?callable $comparer = null): GenericKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);
		$identity = static fn (mixed $element): mixed => $element;

		/**
		 * @var Closure(TSource, TSource): bool $comparer
		 * @var Closure(TSource): TSource $identity
		 */
		return $this->distinctBy($identity, $comparer);
	}

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function distinctBy(callable $keySelector, ?callable $comparer = null): GenericKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		/**
		 * @var Closure(TSource, TSource): bool $comparer
		 * @var Closure(TSource): TSource $keySelector
		 */
		return new KeyedEnumerable(new UniqueByIterator($this->getIterator(), $keySelector(...), $comparer(...)));
	}

	/**
	 * @param GenericKeyedEnumerable<TSource, TIteratorKey> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function except(GenericKeyedEnumerable $other, ?callable $comparer = null): GenericKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return $this->exceptBy($other, static fn (mixed $element): mixed => $element, $comparer);
	}

	/**
	 * @template TCompareKey
	 *
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource, TIteratorKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function exceptBy(GenericKeyedEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new KeyedEnumerable(function () use ($other, $keySelector, $comparer) {
			/** @var Iterator<TCompareKey, TSource> $otherKeys */
			$otherKeys = new CachingIterator(new SelectIterator($other->getIterator(), $keySelector(...)), CachingIterator::FULL_CACHE);

			foreach ($this->getIterator() as $elementKey => $element) {
				$key = $keySelector($element, $elementKey);

				foreach ($otherKeys as $otherKey) {
					if ($comparer($key, $otherKey)) {
						continue 2;
					}
				}

				yield $elementKey => $element;
			}
		});
	}

	public function first(?callable $predicate = null): mixed
	{
		foreach ($this->getIterator() as $elementKey => $element) {
			if ($predicate === null || $predicate($element, $elementKey)) {
				return $element;
			}
		}

		throw new EmptySequenceException();
	}

	public function firstKey(?callable $predicate = null): mixed
	{
		foreach ($this->getIterator() as $elementKey => $element) {
			if ($predicate === null || $predicate($element, $elementKey)) {
				return $elementKey;
			}
		}

		throw new EmptySequenceException();
	}

	public function firstPair(?callable $predicate = null): GenericKeyValuePair
	{
		foreach ($this->getIterator() as $elementKey => $element) {
			if ($predicate === null || $predicate($element, $elementKey)) {
				return new KeyValuePair($elementKey, $element);
			}
		}

		throw new EmptySequenceException();
	}

	/**
	 * @template TDefault
	 *
	 * @param TDefault $defaultValue
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return TDefault|TSource
	 */
	public function firstOrDefault(mixed $defaultValue, ?callable $predicate = null): mixed
	{
		foreach ($this->getIterator() as $elementKey => $element) {
			if ($predicate === null || $predicate($element, $elementKey)) {
				return $element;
			}
		}

		return $defaultValue;
	}

	/**
	 * @template TDefault
	 *
	 * @param TDefault $defaultKey
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return TDefault|TIteratorKey
	 */
	public function firstKeyOrDefault(mixed $defaultKey, ?callable $predicate = null): mixed
	{
		foreach ($this->getIterator() as $elementKey => $element) {
			if ($predicate === null || $predicate($element, $elementKey)) {
				return $elementKey;
			}
		}

		return $defaultKey;
	}

	/**
	 * @param null|GenericKeyValuePair<TIteratorKey, TSource> $defaultPair
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return null|GenericKeyValuePair<TIteratorKey, TSource>
	 */
	public function firstPairOrDefault(?GenericKeyValuePair $defaultPair, ?callable $predicate = null): ?GenericKeyValuePair
	{
		foreach ($this->getIterator() as $elementKey => $element) {
			if ($predicate === null || $predicate($element, $elementKey)) {
				return new KeyValuePair($elementKey, $element);
			}
		}

		return $defaultPair;
	}

	/**
	 * @return GenericKeyedEnumerable<TSource, TIteratorKey>
	 */
	public function flip(): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(new FlipIterator($this->getIterator()));
	}

	/**
	 * @template TGroupKey
	 *
	 * @param callable(TSource): TGroupKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericGroupedKeyedEnumerable<TGroupKey, TIteratorKey, TSource>
	 */
	public function groupBy(callable $keySelector, ?callable $comparer = null): GenericGroupedKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new GroupedKeyedEnumerable(new GroupingIterator($this->getIterator(), $keySelector(...), $comparer(...)));
	}

	/**
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function intersect(GenericKeyedEnumerable $other, ?callable $comparer = null): GenericKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return $this->intersectBy($other, static fn ($element): mixed => $element, $comparer);
	}

	/**
	 * @template TKey
	 *
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function intersectBy(GenericKeyedEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new KeyedEnumerable(function () use ($other, $keySelector, $comparer) {
			$otherKeys = [];
			foreach ($other->getIterator() as $otherElementKey => $otherElement) {
				$otherKeys[] = $keySelector($otherElement, $otherElementKey);
			}

			foreach ($this->getIterator() as $elementKey => $element) {
				$key = $keySelector($element, $elementKey);

				foreach ($otherKeys as $otherKey) {
					if ($comparer($key, $otherKey)) {
						yield $elementKey => $element;

						continue 2;
					}
				}
			}
		});
	}

	public function isEmpty(): bool
	{
		return $this->count() === 0;
	}

	/**
	 * @template TInner
	 * @template TInnerIteratorKey
	 * @template TCompareKey
	 * @template TResult
	 *
	 * @param GenericKeyedEnumerable<TInnerIteratorKey, TInner> $inner
	 * @param callable(TSource, TIteratorKey): TCompareKey $outerKeySelector
	 * @param callable(TInner, TInnerIteratorKey): TCompareKey $innerKeySelector
	 * @param callable(TSource, TInner, TIteratorKey, TInnerIteratorKey): TResult $resultSelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function join(GenericKeyedEnumerable $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): GenericKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new KeyedEnumerable(function () use ($inner, $outerKeySelector, $innerKeySelector, $resultSelector, $comparer) {
			$innerKeys = [];
			$innerElements = [];
			$innerElementKeys = [];
			foreach ($inner->getIterator() as $innerElementKey => $innerElement) {
				$innerKeys[] = $innerKeySelector($innerElement, $innerElementKey);
				$innerElements[] = $innerElement;
				$innerElementKeys[] = $innerElementKey;
			}

			foreach ($this->getIterator() as $outerElementKey => $outerElement) {
				$outerKey = $outerKeySelector($outerElement, $outerElementKey);

				foreach ($innerKeys as $index => $innerKey) {
					if ($comparer($outerKey, $innerKey)) {
						yield $outerElementKey => $resultSelector($outerElement, $innerElements[$index], $outerElementKey, $innerElementKeys[$index]);
					}
				}
			}
		});
	}

	public function last(?callable $predicate = null): mixed
	{
		$last = null;
		foreach ($this->getIterator() as $elementKey => $element) {
			if ($predicate === null || $predicate($element, $elementKey)) {
				$last = $element;
			}
		}

		if ($last === null) {
			throw new EmptySequenceException();
		}

		return $last;
	}

	public function lastOrDefault(mixed $default, ?callable $predicate = null): mixed
	{
		$last = null;
		foreach ($this->getIterator() as $elementKey => $element) {
			if ($predicate === null || $predicate($element, $elementKey)) {
				$last = $element;
			}
		}

		return $last ?? $default;
	}

	/**
	 * @param callable(TSource, TIteratorKey): numeric $selector
	 *
	 * @return numeric
	 */
	public function max(callable $selector): int|float|string
	{
		/** @var Iterator<TIteratorKey, TSource> $iterator */
		$iterator = $this->getIterator();
		$iterator->rewind();
		if (!$iterator->valid()) {
			throw new EmptySequenceException();
		}

		$max = $selector($iterator->current(), $iterator->key());
		$iterator->next();

		while ($iterator->valid()) {
			$max = max($max, $selector($iterator->current(), $iterator->key()));

			$iterator->next();
		}

		return $max;
	}

	/**
	 * @param callable(TSource, TIteratorKey): numeric $selector
	 *
	 * @return numeric
	 */
	public function min(callable $selector): int|float|string
	{
		/** @var Iterator<TIteratorKey, TSource> $iterator */
		$iterator = $this->getIterator();
		$iterator->rewind();
		if (!$iterator->valid()) {
			throw new EmptySequenceException();
		}

		$min = $selector($iterator->current(), $iterator->key());
		$iterator->next();

		while ($iterator->valid()) {
			$min = min($min, $selector($iterator->current(), $iterator->key()));

			$iterator->next();
		}

		return $min;
	}

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, TIteratorKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function orderBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::compare(...);

		return new OrderedEnumerable(new OrderedIterator($this->getIterator(), $keySelector(...), $comparer(...)));
	}

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource, TIteratorKey): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function orderByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::compare(...);
		$comparer = DefaultEqualityComparer::invert($comparer);
		/**
		 * @var Closure(mixed, mixed): int $comparer
		 */

		return new OrderedEnumerable(new OrderedIterator($this->getIterator(), $keySelector(...), $comparer(...)));
	}

	public function prepend(mixed $key, mixed $value): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(function () use ($value, $key) {
			yield $key => $value;

			yield from $this->getIterator();
		});
	}

	public function reverse(): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(new ReverseIterator($this->getIterator()));
	}

	/**
	 * @template TResult
	 *
	 * @param callable(TSource, TIteratorKey): TResult $selector
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TResult>
	 */
	public function select(callable $selector): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(new SelectIterator($this->getIterator(), $selector(...)));
	}

	/**
	 * @template TCollection
	 * @template TCollectionKey
	 * @template TResult
	 *
	 * @param callable(TSource, TIteratorKey): GenericKeyedEnumerable<TCollectionKey, TCollection> $collectionSelector
	 * @param null|callable(TSource, TCollection, TIteratorKey, TCollectionKey): TResult $resultSelector
	 *
	 * @return GenericKeyedEnumerable<TCollectionKey, TResult>
	 */
	public function selectMany(callable $collectionSelector, ?callable $resultSelector = null): GenericKeyedEnumerable
	{
		$resultSelector ??= static fn (mixed $element, mixed $collectionElement, mixed $elementKey, mixed $collectionElementKey): mixed => $collectionElement;
		/** @var callable(TSource, TCollection, TIteratorKey, TCollectionKey): TResult $resultSelector */

		return new KeyedEnumerable(function () use ($collectionSelector, $resultSelector) {
			/**
			 * @var TIteratorKey $elementKey
			 */
			foreach ($this->getIterator() as $elementKey => $element) {
				foreach ($collectionSelector($element, $elementKey) as $collectionElementKey => $collectionElement) {
					yield $collectionElementKey => $resultSelector($element, $collectionElement, $elementKey, $collectionElementKey);
				}
			}
		});
	}

	public function sequenceEqual(GenericKeyedEnumerable $other, ?callable $comparer = null): bool
	{
		$comparer ??= DefaultEqualityComparer::same(...);
		/** @var callable(TSource, TSource, TIteratorKey, TIteratorKey): bool $comparer */
		$mit = new ParallelIterator(ParallelIterator::MIT_KEYS_NUMERIC | ParallelIterator::MIT_NEED_ANY);
		$mit->attachIterator($this->getIterator());
		$mit->attachIterator($other->getIterator());

		foreach ($mit as $keys => $values) {
			/**
			 * @var array{TSource, TSource} $values
			 * @var array{TIteratorKey, TIteratorKey} $keys
			 */
			if (!$comparer($values[0], $values[1], $keys[0], $keys[1])) {
				return false;
			}
		}

		return true;
	}

	public function single(?callable $predicate = null): mixed
	{
		$matched = false;
		$returnElement = null;

		foreach ($this->getIterator() as $elementKey => $element) {
			if ($predicate === null || $predicate($element, $elementKey)) {
				if ($matched) {
					throw new AmbiguousMatchException();
				}

				$matched = true;
				$returnElement = $element;
			}
		}

		if (!$matched) {
			throw new EmptySequenceException();
		}

		return $returnElement;
	}

	/**
	 * @param TSource $default
	 * @param null|callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return TSource
	 */
	public function singleOrDefault(mixed $default, ?callable $predicate = null): mixed
	{
		$matched = false;
		$returnElement = null;

		foreach ($this->getIterator() as $elementKey => $element) {
			if ($predicate === null || $predicate($element, $elementKey)) {
				if ($matched) {
					throw new AmbiguousMatchException();
				}

				$matched = true;
				$returnElement = $element;
			}
		}

		return $matched ? $returnElement : $default;
	}

	public function skip(int $count): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(new LimitIterator($this->getIterator(), $count));
	}

	public function skipLast(int $count): GenericKeyedEnumerable
	{
		if ($count <= 0) {
			throw new InvalidArgumentException('Count must be greater than zero');
		}

		$cachedIterator = new CachingIterator($this->getIterator(), CachingIterator::FULL_CACHE);
		$cachedIterator->rewind();
		while ($cachedIterator->valid()) {
			$cachedIterator->next();
		}

		$size = count($cachedIterator);
		$offset = $size - $count;
		if ($offset > 0) {
			$iterator = new LimitIterator($cachedIterator, 0, $offset);
		} else {
			$iterator = new EmptyIterator();
		}

		return new KeyedEnumerable($iterator);
	}

	/**
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function skipWhile(callable $predicate): GenericKeyedEnumerable
	{
		/** @var Iterator<TIteratorKey, TSource> $iterator */
		$iterator = $this->getIterator();

		$whileIterator = new WhileIterator($iterator, $predicate(...));
		$whileIterator->rewind();
		while ($whileIterator->valid()) {
			$whileIterator->next();
		}

		return new KeyedEnumerable(new NoRewindIterator($iterator));
	}

	/**
	 * @param callable(TSource, TIteratorKey): numeric $selector
	 *
	 * @return numeric
	 */
	public function sum(callable $selector): int|float|string
	{
		/** @var numeric */
		return $this->aggregate(static function (mixed $accumulator, mixed $element, mixed $elementKey) use ($selector) {
			/**
			 * @var numeric $accumulator
			 * @var TSource $element
			 * @var TIteratorKey $elementKey
			 */
			return $accumulator + $selector($element, $elementKey);
		}, 0);
	}

	public function take(int $count): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(new LimitIterator($this->getIterator(), 0, $count));
	}

	public function takeLast(int $count): GenericKeyedEnumerable
	{
		$cachedIterator = new CachingIterator($this->getIterator(), CachingIterator::FULL_CACHE);
		$cachedIterator->rewind();
		while ($cachedIterator->valid()) {
			$cachedIterator->next();
		}

		$size = count($cachedIterator);
		$offset = $size - $count;
		if ($offset < 0) {
			return new KeyedEnumerable(new EmptyIterator());
		}

		return new KeyedEnumerable(new LimitIterator($cachedIterator, $offset));
	}

	/**
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function takeWhile(callable $predicate): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(new WhileIterator($this->getIterator(), $predicate(...)));
	}

	/**
	 * @return list<TSource>
	 */
	public function toList(): array
	{
		$list = [];

		/** @var TSource $element */
		foreach ($this->getIterator() as $element) {
			$list[] = $element;
		}

		return $list;
	}

	/**
	 * @return ArrayList<TSource>
	 */
	public function toArrayList(): ArrayList
	{
		return new ArrayList($this->toList());
	}

	public function toArray(?callable $keySelector = null): array
	{
		$keySelector ??= static fn (mixed $key, mixed $value): mixed => $key;

		$array = [];

		foreach ($this->getIterator() as $elementKey => $element) {
			$key = $keySelector($elementKey, $element);

			if ($key instanceof Stringable) {
				$key = (string) $key;
			}

			if (!is_scalar($key)) {
				throw new OutOfBoundsException('Invalid array key: ' . get_debug_type($key));
			}

			/** @var array-key $key */
			$array[$key] = $element;
		}

		return $array;
	}

	public function toNestedArray(?callable $keySelector = null): array
	{
		$keySelector ??= static fn (mixed $key, mixed $value): mixed => $key;

		$array = [];

		/**
		 * @var TIteratorKey $elementKey
		 * @var TSource $element
		 */
		foreach ($this->getIterator() as $elementKey => $element) {
			$key = $keySelector($elementKey, $element);

			if ($key instanceof Stringable) {
				$key = (string) $key;
			}

			if (!is_scalar($key)) {
				throw new OutOfBoundsException('Invalid array key: ' . get_debug_type($key));
			}

			/**
			 * @var array-key $key
			 */
			$array[$key][] = $element;
		}

		return $array;
	}

	public function keys(): GenericEnumerable
	{
		return new Enumerable(new FlipIterator($this->getIterator()));
	}

	public function values(): GenericEnumerable
	{
		return new Enumerable($this->getIterator());
	}

	/**
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function union(GenericKeyedEnumerable $other, ?callable $comparer = null): GenericKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);
		$identity = static fn (mixed $o): mixed => $o;

		/**
		 * @var callable(TSource, TSource): bool $comparer
		 * @var callable(TSource): TSource $identity
		 */
		return $this->unionBy($other, $identity, $comparer);
	}

	/**
	 * @template TCompareKey
	 *
	 * @param GenericKeyedEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function unionBy(GenericKeyedEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		$append = new AppendIterator();
		$append->append($this->getIterator());
		$append->append($other->getIterator());

		/**
		 * @var Closure(TSource): TCompareKey $keySelector
		 * @var Closure(TCompareKey, TCompareKey): bool $comparer
		 */
		return new KeyedEnumerable(new UniqueByIterator($append, $keySelector(...), $comparer(...)));
	}

	/**
	 * @param callable(TSource, TIteratorKey, Iterator<TIteratorKey, TSource>): bool $predicate
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function where(callable $predicate): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(new CallbackFilterIterator($this->getIterator(), $predicate(...)));
	}

	/**
	 * @param callable(TIteratorKey, TSource, Iterator<TSource, TIteratorKey>): bool $predicate
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function whereKey(callable $predicate): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(new FlipIterator(new CallbackFilterIterator(new FlipIterator($this->getIterator()), $predicate(...))));
	}

	/**
	 * @template TOther
	 * @template TOtherIteratorKey
	 * @template TResult
	 * @template TResultKey
	 *
	 * @param GenericKeyedEnumerable<TOtherIteratorKey, TOther> $other
	 * @param null|callable(TSource, TOther): TResult $resultSelector
	 * @param null|callable(TIteratorKey, TOtherIteratorKey): TResultKey $keySelector
	 *
	 * @return GenericKeyedEnumerable<TResultKey, TResult>
	 */
	public function zip(GenericKeyedEnumerable $other, ?callable $resultSelector = null, ?callable $keySelector = null): GenericKeyedEnumerable
	{
		$resultSelector ??= static fn (mixed $a, mixed $b): array => [$a, $b];
		$keySelector ??= static fn (mixed $a, mixed $b): mixed => $a;

		$mit = new ParallelIterator(ParallelIterator::MIT_KEYS_NUMERIC | ParallelIterator::MIT_NEED_ALL);
		$mit->attachIterator($this->getIterator());
		$mit->attachIterator($other->getIterator());
		/** @var ParallelIterator $mit */

		/** @var GenericKeyedEnumerable<TResultKey, TResult> */
		return new KeyedEnumerable(
			/** @var SelectIterator<TResultKey, TResult> */
			new SelectIterator(
				/** @var KeySelectIterator<TResultKey, array{TSource, TOther}> */
				new KeySelectIterator(
					$mit,
					static function (mixed $keys) use ($keySelector): mixed {
						/** @var array{TIteratorKey, TOtherIteratorKey} $keys */
						return $keySelector($keys[0], $keys[1]);
					},
				),
				static function (mixed $values) use ($resultSelector): array {
					/** @var array{TSource, TOther} $values */
					return $resultSelector($values[0], $values[1]);
				},
			),
		);
	}
}
