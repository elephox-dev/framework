<?php
declare(strict_types=1);

namespace Elephox\Collection;

use AppendIterator;
use CachingIterator;
use CallbackFilterIterator;
use Countable;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Contract\GenericOrderedEnumerable;
use EmptyIterator;
use Generator;
use InvalidArgumentException;
use Iterator;
use LimitIterator;
use MultipleIterator as ParallelIterator;
use NoRewindIterator;

/**
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TSource
 */
trait IsEnumerable
{
	/**
	 * @return Iterator<mixed, TSource>
	 */
	abstract public function getIterator(): Iterator;

	/**
	 * @template TAccumulate
	 *
	 * @param callable(TAccumulate|null, TSource): TAccumulate $accumulator
	 * @param TAccumulate|null $seed
	 *
	 * @return TAccumulate
	 */
	public function aggregate(callable $accumulator, mixed $seed = null): mixed
	{
		$result = $seed;

		foreach ($this->getIterator() as $element) {
			$result = $accumulator($result, $element);
		}

		return $result;
	}

	public function all(callable $predicate): bool
	{
		foreach ($this->getIterator() as $element) {
			if (!$predicate($element)) {
				return false;
			}
		}

		return true;
	}

	public function any(callable $predicate = null): bool
	{
		foreach ($this->getIterator() as $element) {
			if ($predicate === null || $predicate($element)) {
				return true;
			}
		}

		return false;
	}

	public function append(mixed $value): GenericEnumerable
	{
		return new Enumerable(function () use ($value) {
			yield from $this->getIterator();

			yield $value;
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

		foreach ($this->getIterator() as $element) {
			$value = $selector($element);

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

			foreach ($this->getIterator() as $element) {
				if (count($chunk) === $size) {
					yield $chunk;

					$chunk = [$element];
				} else {
					$chunk[] = $element;
				}
			}

			if (!empty($chunk)) {
				yield $chunk;
			}
		});
	}

	public function concat(GenericEnumerable ...$other): GenericEnumerable
	{
		return new Enumerable(function () use ($other) {
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
	 * @param null|callable(TSource): bool $predicate
	 *
	 * @return NonNegativeInteger
	 */
	public function count(callable $predicate = null): int
	{
		$iterator = $this->getIterator();
		if ($predicate !== null) {
			$iterator = new CallbackFilterIterator($iterator, $predicate);
		} else if ($iterator instanceof Countable) {
			/** @var NonNegativeInteger */
			return $iterator->count();
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
	 * @return GenericEnumerable<TSource>
	 */
	public function distinct(?callable $comparer = null): GenericEnumerable
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
	 * @return GenericEnumerable<TSource>
	 */
	public function distinctBy(callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		/**
		 * @var Closure(TSource, TSource): bool $comparer
		 * @var Closure(TSource): TSource $keySelector
		 */
		return new Enumerable(new UniqueByIterator($this->getIterator(), $keySelector(...), $comparer(...)));
	}

	/**
	 * @param GenericEnumerable<TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function except(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return $this->exceptBy($other, fn(mixed $element): mixed => $element, $comparer);
	}

	/**
	 * @template TCompareKey
	 *
	 * @param GenericEnumerable<TSource> $other
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function exceptBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		/** @var Iterator<mixed, TSource> $otherIterator */
		$otherIterator = $other->getIterator();

		return new Enumerable(function () use ($otherIterator, $keySelector, $comparer) {
			/** @var Iterator<mixed, TCompareKey> $otherKeys */
			$otherKeys = new CachingIterator(new SelectIterator($otherIterator, $keySelector(...)), CachingIterator::FULL_CACHE);

			foreach ($this->getIterator() as $element) {
				$key = $keySelector($element);

				foreach ($otherKeys as $otherKey) {
					if ($comparer($key, $otherKey)) {
						continue 2;
					}
				}

				yield $element;
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

	/**
	 * @param GenericEnumerable<TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function intersect(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return $this->intersectBy($other, fn($element): mixed => $element, $comparer);
	}

	/**
	 * @template TKey
	 *
	 * @param GenericEnumerable<TSource> $other
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function intersectBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new Enumerable(function () use ($other, $keySelector, $comparer) {
			$otherKeys = [];
			foreach ($other->getIterator() as $otherElement) {
				$otherKeys[] = $keySelector($otherElement);
			}

			foreach ($this->getIterator() as $element) {
				$key = $keySelector($element);

				foreach ($otherKeys as $otherKey) {
					if ($comparer($key, $otherKey)) {
						yield $element;

						continue 2;
					}
				}
			}
		});
	}

	/**
	 * @template TInner
	 * @template TCompareKey
	 * @template TResult
	 *
	 * @param GenericEnumerable<TInner> $inner
	 * @param callable(TSource): TCompareKey $outerKeySelector
	 * @param callable(TInner): TCompareKey $innerKeySelector
	 * @param callable(TSource, TInner): TResult $resultSelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function join(GenericEnumerable $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new Enumerable(function () use ($inner, $outerKeySelector, $innerKeySelector, $resultSelector, $comparer): Generator {
			$innerKeys = [];
			$innerElements = [];
			foreach ($inner->getIterator() as $innerElement) {
				$innerKeys[] = $innerKeySelector($innerElement);
				$innerElements[] = $innerElement;
			}

			foreach ($this->getIterator() as $outerElement) {
				$outerKey = $outerKeySelector($outerElement);

				foreach ($innerKeys as $index => $innerKey) {
					if ($comparer($outerKey, $innerKey)) {
						yield $resultSelector($outerElement, $innerElements[$index]);
					}
				}
			}
		});
	}

	public function last(?callable $predicate = null): mixed
	{
		$last = null;
		foreach ($this->getIterator() as $element) {
			if ($predicate === null || $predicate($element)) {
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

		while ($iterator->valid()) {
			$min = min($min, $selector($iterator->current()));

			$iterator->next();
		}

		return $min;
	}

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource>
	 */
	public function orderBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::compare(...);

		$keys = [];
		$elements = [];

		foreach ($this->getIterator() as $element) {
			$key = $keySelector($element);

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
	 * @param callable(TSource): TCompareKey $keySelector
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

	public function prepend(mixed $value): GenericEnumerable
	{
		return new Enumerable(function () use ($value) {
			yield $value;

			yield from $this->getIterator();
		});
	}

	public function reverse(): GenericEnumerable
	{
		return new Enumerable(new ReverseIterator($this->getIterator()));
	}

	/**
	 * @template TResult
	 *
	 * @param callable(TSource): TResult $selector
	 *
	 * @return GenericEnumerable<TResult>
	 */
	public function select(callable $selector): GenericEnumerable
	{
		return new Enumerable(new SelectIterator($this->getIterator(), $selector(...)));
	}

	/**
	 * @template TCollection
	 * @template TCollectionKey
	 * @template TResult
	 *
	 * @param callable(TSource): GenericKeyedEnumerable<TCollectionKey, TCollection> $collectionSelector
	 * @param null|callable(TSource, TCollection, TCollectionKey): TResult $resultSelector
	 *
	 * @return GenericKeyedEnumerable<TCollectionKey, TResult>
	 */
	public function selectMany(callable $collectionSelector, ?callable $resultSelector = null): GenericKeyedEnumerable
	{
		/** @psalm-suppress UnusedClosureParam */
		$resultSelector ??= static fn(mixed $element, mixed $collectionElement, mixed $collectionElementKey): mixed => $collectionElement;
		/** @var callable(TSource, TCollection, TCollectionKey): TResult $resultSelector */

		return new KeyedEnumerable(function () use ($collectionSelector, $resultSelector) {
			foreach ($this->getIterator() as $element) {
				foreach ($collectionSelector($element) as $collectionElementKey => $collectionElement) {
					yield $collectionElementKey => $resultSelector($element, $collectionElement, $collectionElementKey);
				}
			}
		});
	}

	public function sequenceEqual(GenericEnumerable $other, ?callable $comparer = null): bool
	{
		$comparer ??= DefaultEqualityComparer::same(...);
		/** @var callable(TSource, TSource): bool $comparer */

		/** @var Iterator<mixed, TSource> $otherIterator */
		$otherIterator = $other->getIterator();

		$mit = new ParallelIterator(ParallelIterator::MIT_KEYS_NUMERIC | ParallelIterator::MIT_NEED_ANY);
		$mit->attachIterator($this->getIterator());
		$mit->attachIterator($otherIterator);

		foreach ($mit as $values) {
			/**
			 * @var array{TSource, TSource} $values
			 */
			if (!$comparer($values[0], $values[1])) {
				return false;
			}
		}

		return true;
	}

	public function single(?callable $predicate = null): mixed
	{
		$matched = false;
		$returnElement = null;

		foreach ($this->getIterator() as $element) {
			if ($predicate === null || $predicate($element)) {
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

		foreach ($this->getIterator() as $element) {
			if ($predicate === null || $predicate($element)) {
				if ($matched) {
					throw new AmbiguousMatchException();
				}

				$matched = true;
				$returnElement = $element;
			}
		}

		return $matched ? $returnElement : $default;
	}

	public function skip(int $count): GenericEnumerable
	{
		return new Enumerable(new LimitIterator($this->getIterator(), $count));
	}

	public function skipLast(int $count): GenericEnumerable
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

		return new Enumerable($iterator);
	}

	/**
	 * @param callable(TSource): bool $predicate
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function skipWhile(callable $predicate): GenericEnumerable
	{
		$iterator = $this->getIterator();

		$whileIterator = new WhileIterator($iterator, $predicate(...));
		$whileIterator->rewind();
		while ($whileIterator->valid()) {
			$whileIterator->next();
		}

		return new Enumerable(new NoRewindIterator($iterator));
	}

	/**
	 * @param callable(TSource): numeric $selector
	 *
	 * @return numeric
	 */
	public function sum(callable $selector): int|float|string
	{
		/** @var numeric */
		return $this->aggregate(function (mixed $accumulator, mixed $element) use ($selector) {
			/**
			 * @var numeric $accumulator
			 * @var TSource $element
			 */
			return $accumulator + $selector($element);
		}, 0);
	}

	public function take(int $count): GenericEnumerable
	{
		return new Enumerable(new LimitIterator($this->getIterator(), 0, $count));
	}

	public function takeLast(int $count): GenericEnumerable
	{
		$cachedIterator = new CachingIterator($this->getIterator(), CachingIterator::FULL_CACHE);
		$cachedIterator->rewind();
		while ($cachedIterator->valid()) {
			$cachedIterator->next();
		}

		$size = count($cachedIterator);
		$offset = $size - $count;
		if ($offset < 0) {
			return new Enumerable(new EmptyIterator());
		}

		return new Enumerable(new LimitIterator($cachedIterator, $offset));
	}

	/**
	 * @param callable(TSource): bool $predicate
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function takeWhile(callable $predicate): GenericEnumerable
	{
		return new Enumerable(new WhileIterator($this->getIterator(), $predicate(...)));
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
	 * @template TArrayKey as array-key
	 *
	 * @param null|callable(TSource): TArrayKey $keySelector
	 *
	 * @return array<TArrayKey, TSource>
	 */
	public function toArray(?callable $keySelector = null): array
	{
		return self::reindex($this->getIterator())->toArray($keySelector);
	}

	public function toKeyed(callable $keySelector): GenericKeyedEnumerable
	{
		/** @psalm-suppress UnusedClosureParam */
		$valueProxy = static fn (mixed $key, mixed $value): mixed => $keySelector($value);

		return new KeyedEnumerable(new KeySelectIterator($this->getIterator(), $valueProxy(...)));
	}

	public function union(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable
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
	 * @param GenericEnumerable<TSource> $other
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function unionBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		/** @var Iterator<mixed, TSource> $otherIterator */
		$otherIterator = $other->getIterator();

		$append = new AppendIterator();
		$append->append($this->getIterator());
		$append->append($otherIterator);

		/**
		 * @var Closure(TSource): TCompareKey $keySelector
		 * @var Closure(TCompareKey, TCompareKey): bool $comparer
		 */
		return new Enumerable(new UniqueByIterator($append, $keySelector(...), $comparer(...)));
	}

	/**
	 * @param callable(TSource): bool $predicate
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function where(callable $predicate): GenericEnumerable
	{
		/** @var Iterator<mixed, TSource> $iterator */
		$iterator = $this->getIterator();

		return new Enumerable(new CallbackFilterIterator($iterator, $predicate(...)));
	}

	/**
	 * @template TOther
	 * @template TResult
	 *
	 * @param GenericEnumerable<TOther> $other
	 * @param null|callable(TSource, TOther): TResult $resultSelector
	 *
	 * @return GenericEnumerable<TResult>
	 */
	public function zip(GenericEnumerable $other, ?callable $resultSelector = null): GenericEnumerable
	{
		$resultSelector ??= static fn(mixed $a, mixed $b): array => [$a, $b];

		/** @var Iterator<mixed, TSource> $otherIterator */
		$otherIterator = $other->getIterator();

		$mit = new ParallelIterator(ParallelIterator::MIT_KEYS_NUMERIC | ParallelIterator::MIT_NEED_ALL);
		$mit->attachIterator($this->getIterator());
		$mit->attachIterator($otherIterator);
		/** @var ParallelIterator $mit */

		/** @var GenericEnumerable<TResult> */
		return new Enumerable(
		/** @var SelectIterator<mixed, TResult> */
			new SelectIterator(
				$mit,
				static function (mixed $values) use ($resultSelector): mixed {
					/** @var array{TSource, TOther} $values */
					return $resultSelector($values[0], $values[1]);
				}
			)
		);
	}
}
