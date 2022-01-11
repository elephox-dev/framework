<?php
declare(strict_types=1);

namespace Elephox\PIE;

use AppendIterator;
use CachingIterator;
use CallbackFilterIterator;
use Countable;
use EmptyIterator;
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
	 * @template USource
	 * @template UKey
	 *
	 * @param Iterator<UKey, USource> $iterator
	 * @return GenericKeyedEnumerable<NonNegativeInteger, USource>
	 */
	private static function reindex(Iterator $iterator): GenericKeyedEnumerable
	{
		$key = 0;

		return new KeyedEnumerable(new KeySelectIterator($iterator, function () use (&$key): int {
			/**
			 * @var NonNegativeInteger $key
			 */
			return $key++;
		}));
	}

	/**
	 * @return Iterator<TIteratorKey, TSource>
	 */
	abstract public function getIterator(): Iterator;

	/**
	 * @template TAccumulate
	 *
	 * @param callable(TAccumulate|null, TSource, TIteratorKey): TAccumulate $accumulator
	 * @param TAccumulate|null $seed
	 *
	 * @return TAccumulate
	 */
	public function aggregate(callable $accumulator, mixed $seed = null): mixed
	{
		$result = $seed;

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

	public function any(callable $predicate = null): bool
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

	/**
	 * @param callable(TSource): numeric $selector
	 *
	 * @return numeric
	 */
	public function average(callable $selector): int|float|string
	{
		$sum = null;
		$count = 0;

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
	 * @return GenericEnumerable<list<TSource>>
	 */
	public function chunk(int $size): GenericEnumerable
	{
		return new Enumerable(function () use ($size) {
			$chunk = [];
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
	 * @psalm-suppress MoreSpecificImplementedParamType Psalm thinks the template params are set from OrderedEnumerable...
	 *
	 * @param null|callable(TSource, TIteratorKey, Iterator<TIteratorKey, TSource>): bool $predicate
	 *
	 * @return NonNegativeInteger
	 */
	public function count(callable $predicate = null): int
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

		/**
		 * This can be removed once vimeo/psalm#7331 is resolved
		 * @var NonNegativeInteger
		 */
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
		$identity = static fn(mixed $element): mixed => $element;

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

		return $this->exceptBy($other, fn (mixed $element): mixed => $element, $comparer);
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
		foreach ($this->getIterator() as $element) {
			if ($predicate === null || $predicate($element)) {
				return $element;
			}
		}

		throw new EmptySequenceException();
	}

	public function firstOrDefault(mixed $defaultValue, ?callable $predicate = null): mixed
	{
		foreach ($this->getIterator() as $element) {
			if ($predicate === null || $predicate($element)) {
				return $element;
			}
		}

		return $defaultValue;
	}

	public function flip(): GenericKeyedEnumerable
	{
		return new KeyedEnumerable(new FlipIterator($this->getIterator()));
	}

	/**
	 * @param GenericKeyedEnumerable<TSource, TIteratorKey> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericKeyedEnumerable<TIteratorKey, TSource>
	 */
	public function intersect(GenericKeyedEnumerable $other, ?callable $comparer = null): GenericKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return $this->intersectBy($other, fn ($element): mixed => $element, $comparer);
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
		foreach ($this->getIterator() as $element) {
			if ($predicate === null || $predicate($element)) {
				$last = $element;
			}
		}

		return $last ?? $default;
	}

	/**
	 * @param callable(TSource): numeric $selector
	 *
	 * @return numeric
	 */
	public function max(callable $selector): int|float|string
	{
		$iterator = $this->getIterator();
		$iterator->rewind();
		if (!$iterator->valid()) {
			throw new EmptySequenceException();
		}

		$max = $selector($iterator->current());
		$iterator->next();

		while ($iterator->valid()) {
			$max = max($max, $selector($iterator->current()));

			$iterator->next();
		}

		return $max;
	}

	/**
	 * @param callable(TSource): numeric $selector
	 *
	 * @return numeric
	 */
	public function min(callable $selector): int|float|string
	{
		$iterator = $this->getIterator();
		$iterator->rewind();
		if (!$iterator->valid()) {
			throw new EmptySequenceException();
		}

		$min = $selector($iterator->current());
		$iterator->next();

		while($iterator->valid()) {
			$min = min($min, $selector($iterator->current()));

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

		$keys = [];
		$elements = [];

		foreach ($this->getIterator() as $elementKey => $element) {
			$key = $keySelector($element, $elementKey);

			$keys[] = $key;
			$elements[] = $element;
		}

		$unsortedKeys = $keys;
		usort($keys, $comparer);

		return new OrderedEnumerable(function () use ($keys, $elements, $unsortedKeys) {
			$newIndex = 0;
			foreach ($keys as $key) {
				$unsortedIndex = array_search($key, $unsortedKeys, true);

				yield $newIndex++ => $elements[$unsortedIndex];
			}
		});
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
		/** @var callable(TCompareKey, TCompareKey): int $comparer */

		$invertedComparer = DefaultEqualityComparer::invert($comparer);
		/** @var callable(TCompareKey, TCompareKey): int $invertedComparer */

		return $this->orderBy($keySelector, $invertedComparer);
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
		/** @psalm-suppress UnusedClosureParam */
		$resultSelector ??= static fn (mixed $element, mixed $collectionElement, mixed $elementKey, mixed $collectionElementKey): mixed => $collectionElement;
		/** @var callable(TSource, TCollection, TIteratorKey, TCollectionKey): TResult $resultSelector */

		return new KeyedEnumerable(function () use ($collectionSelector, $resultSelector) {
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
		$cachedIterator = new CachingIterator($this->getIterator(), CachingIterator::FULL_CACHE);
		$cachedIterator->rewind();
		while ($cachedIterator->valid()) {
			$cachedIterator->next();
		}

		$size = count($cachedIterator);
		$offset = $size - $count;
		if ($offset >= 0) {
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
		$iterator = $this->getIterator();

		$whileIterator = new WhileIterator($iterator, $predicate(...));
		$whileIterator->rewind();
		while ($whileIterator->valid()) {
			$whileIterator->next();
		}

		return new KeyedEnumerable(new NoRewindIterator($iterator));
	}

	/**
	 * @param callable(TSource): numeric $selector
	 *
	 * @return numeric
	 */
	public function sum(callable $selector): int|float|string
	{
		/** @var numeric */
		return $this->aggregate(function (mixed $accumulator, mixed $element, mixed $elementKey) use ($selector) {
			/**
			 * @var numeric $accumulator
			 * @var TSource $element
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

		foreach ($this->getIterator() as $element) {
			$list[] = $element;
		}

		return $list;
	}

	public function toArray(?callable $keySelector = null): array
	{
		$keySelector ??= static fn(mixed $value, mixed $key): mixed => $key;

		$array = [];

		foreach ($this->getIterator() as $elementKey => $element) {
			$key = $keySelector($element, $elementKey);

			if ($key instanceof Stringable) {
				$key = (string)$key;
			}

			if (!is_scalar($key)) {
				throw new OutOfBoundsException('Invalid array key: ' . get_debug_type($key));
			}

			/** @var array-key $key */
			$array[$key] = $element;
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

	public function union(GenericKeyedEnumerable $other, ?callable $comparer = null): GenericKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);
		$identity = static fn(mixed $o): mixed => $o;

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
		/** @psalm-suppress UnusedClosureParam */
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
				static function (mixed $values) use ($resultSelector): mixed {
					/** @var array{TSource, TOther} $values */
					return $resultSelector($values[0], $values[1]);
				}
			)
		);
	}
}