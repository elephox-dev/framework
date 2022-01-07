<?php
declare(strict_types=1);

namespace Elephox\PIE;

use AppendIterator;
use CachingIterator;
use CallbackFilterIterator;
use Countable;
use EmptyIterator;
use InvalidArgumentException;
use Iterator;
use LimitIterator;
use MultipleIterator;
use NoRewindIterator;
use UnexpectedValueException;

/**
 * @psalm-suppress LessSpecificImplementedReturnType For some reason, psalm thinks every enumerable has the templates of OrderedEnumerable...
 * @psalm-type NonNegativeInteger = 0|positive-int
 *
 * @template TIteratorKey
 * @template TSource
 */
trait IsEnumerable
{
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

		foreach ($this->getIterator() as $key => $element) {
			$result = $accumulator($result, $element, $key);
		}

		return $result;
	}

	// TODO: rewrite some functions to use aggregate

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
		foreach ($this->getIterator() as $key => $element) {
			if ($predicate === null || $predicate($element, $key)) {
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

		if ($sum === null) {
			throw new InvalidArgumentException('The sequence contains no elements');
		}

		/** @var numeric $sum */
		return $sum / $count;
	}

	/**
	 * @param NonNegativeInteger $size
	 *
	 * @return GenericEnumerable<NonNegativeInteger, list<TSource>>
	 */
	public function chunk(int $size): GenericEnumerable
	{
		/** @var GenericEnumerable<NonNegativeInteger, list<TSource>> */
		return new Enumerable(function () use ($size) {
			$chunkCount = 0;
			$chunk = [];
			foreach ($this->getIterator() as $element) {
				if (count($chunk) === $size) {
					yield $chunkCount => $chunk;

					$chunkCount++;
					$chunk = [$element];
				} else {
					$chunk[] = $element;
				}
			}

			if ($chunk) {
				yield $chunkCount => $chunk;
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
	 * @return GenericEnumerable<TIteratorKey, TSource>
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
	 * @template TKey
	 *
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TKey, TKey): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function distinctBy(callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new Enumerable(new UniqueByIterator($this->getIterator(), $keySelector, $comparer));
	}

	public function elementAt(int $index): mixed
	{
		$enumerator = $this->getIterator();

		foreach ($enumerator as $element) {
			if ($index === 0) {
				return $element;
			}

			$index--;
		}

		throw new InvalidArgumentException('Index out of range');
	}

	/**
	 * @noinspection PhpPureAttributeCanBeAddedInspection
	 */
	public function elementAtOrDefault(int $index, mixed $defaultValue): mixed
	{
		$enumerator = $this->getIterator();

		foreach ($enumerator as $element) {
			if ($index === 0) {
				return $element;
			}

			$index--;
		}

		return $defaultValue;
	}

	/**
	 * @param GenericEnumerable<TSource, TIteratorKey> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function except(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return $this->exceptBy($other, fn (mixed $element): mixed => $element, $comparer);
	}

	/**
	 * @template TKey
	 *
	 * @param GenericEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TKey, TKey): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function exceptBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new Enumerable(function () use ($other, $keySelector, $comparer) {
			$otherKeys = [];
			foreach ($other->getIterator() as $otherElementKey => $otherElement) {
				$otherKeys[] = $keySelector($otherElement, $otherElementKey);
			}

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

		throw new InvalidArgumentException('Sequence contains no matching element');
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

	public function flip(): GenericEnumerable
	{
		return new Enumerable(new FlipIterator($this->getIterator()));
	}

//	public function groupBy(callable $keySelector, ?callable $elementSelector = null, ?callable $resultSelector = null, ?callable $comparer = null): GenericEnumerable
//	{
//		$comparer ??= DefaultEqualityComparer::same(...);
//
//		// TODO: Implement groupBy() method.
//	}
//
//	public function groupJoin(GenericEnumerable $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): GenericEnumerable
//	{
//		$comparer ??= DefaultEqualityComparer::same(...);
//
//		// TODO: Implement groupJoin() method.
//	}

	/**
	 * @param GenericEnumerable<TSource, TIteratorKey> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function intersect(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return $this->intersectBy($other, fn ($element): mixed => $element, $comparer);
	}

	/**
	 * @template TKey
	 *
	 * @param GenericEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function intersectBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new Enumerable(function () use ($other, $keySelector, $comparer) {
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
	 * @template TKey
	 * @template TResult
	 *
	 * @param GenericEnumerable<TInnerIteratorKey, TInner> $inner
	 * @param callable(TSource, TIteratorKey): TKey $outerKeySelector
	 * @param callable(TInner, TInnerIteratorKey): TKey $innerKeySelector
	 * @param callable(TSource, TInner, TIteratorKey, TInnerIteratorKey): TResult $resultSelector
	 * @param null|callable(TKey, TKey): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function join(GenericEnumerable $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new Enumerable(function () use ($inner, $outerKeySelector, $innerKeySelector, $resultSelector, $comparer) {
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
		foreach ($this->getIterator() as $element) {
			if ($predicate === null || $predicate($element)) {
				$last = $element;
			}
		}

		if ($last === null) {
			throw new InvalidArgumentException('Sequence contains no matching element');
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
			throw new InvalidArgumentException('Sequence contains no elements');
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
			throw new InvalidArgumentException('Sequence contains no elements');
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
	 * @return GenericOrderedEnumerable<NonNegativeInteger, TSource>
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
	 * @return GenericOrderedEnumerable<NonNegativeInteger, TSource>
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
	 * @param callable(TSource, TIteratorKey): TResult $selector
	 *
	 * @return GenericEnumerable<TIteratorKey, TResult>
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
	 * @param callable(TSource, TIteratorKey): GenericEnumerable<TCollectionKey, TCollection> $collectionSelector
	 * @param null|callable(TSource, TCollection, TIteratorKey, TCollectionKey): TResult $resultSelector
	 *
	 * @return GenericEnumerable<TCollectionKey, TResult>
	 */
	public function selectMany(callable $collectionSelector, ?callable $resultSelector = null): GenericEnumerable
	{
		/** @psalm-suppress UnusedClosureParam */
		$resultSelector ??= static fn (mixed $element, mixed $collectionElement, mixed $elementKey, mixed $collectionElementKey): mixed => $collectionElement;
		/** @var callable(TSource, TCollection, TIteratorKey, TCollectionKey): TResult $resultSelector */

		return new Enumerable(function () use ($collectionSelector, $resultSelector) {
			foreach ($this->getIterator() as $elementKey => $element) {
				foreach ($collectionSelector($element, $elementKey) as $collectionElementKey => $collectionElement) {
					yield $collectionElementKey => $resultSelector($element, $collectionElement, $elementKey, $collectionElementKey);
				}
			}
		});
	}

	public function sequenceEqual(GenericEnumerable $other, ?callable $comparer = null): bool
	{
		$comparer ??= DefaultEqualityComparer::same(...);
		/** @var callable(TSource, TSource, TIteratorKey, TIteratorKey): bool $comparer */

		$mit = new MultipleIterator(MultipleIterator::MIT_KEYS_NUMERIC | MultipleIterator::MIT_NEED_ANY);
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
					throw new InvalidArgumentException('Sequence contains more than one matching element');
				}

				$matched = true;
				$returnElement = $element;
			}
		}

		if (!$matched) {
			throw new InvalidArgumentException('Sequence contains no matching element');
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
					throw new InvalidArgumentException('Sequence contains more than one matching element');
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
		if ($offset < 0) {
			return new Enumerable(new EmptyIterator());
		}

		return new Enumerable(new LimitIterator($cachedIterator, 0, $offset));
	}

	/**
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
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
	 * @param callable(TSource, TIteratorKey): bool $predicate
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
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

	public function toArray(): array
	{
		$array = [];

		foreach ($this->getIterator() as $elementKey => $element) {
			if (is_scalar($elementKey)) {
				$key = $elementKey;
			} else if (is_array($elementKey)) {
				throw new UnexpectedValueException('Cannot use array as array key');
			} else {
				$key = (string)$elementKey;
			}

			/** @var array-key $key */
			$array[$key] = $element;
		}

		return $array;
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
	 * @template TKey
	 *
	 * @param GenericEnumerable<TIteratorKey, TSource> $other
	 * @param callable(TSource): TKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function unionBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		$append = new AppendIterator();
		$append->append($this->getIterator());
		$append->append($other->getIterator());

		/**
		 * @var Closure(TSource): TKey $keySelector
		 * @var Closure(TKey, TKey): bool $comparer
		 */
		return new Enumerable(new UniqueByIterator($append, $keySelector(...), $comparer(...)));
	}

	/**
	 * @param callable(TSource, TIteratorKey, Iterator<TIteratorKey, TSource>): bool $predicate
	 *
	 * @psalm-suppress MoreSpecificImplementedParamType mixed is not more specific than TSource
	 * @return GenericEnumerable<TIteratorKey, TSource>
	 */
	public function where(callable $predicate): GenericEnumerable
	{
		return new Enumerable(new CallbackFilterIterator($this->getIterator(), $predicate(...)));
	}

	/**
	 * @template TOther
	 * @template TOtherIteratorKey
	 * @template TResult
	 * @template TResultKey
	 *
	 * @param GenericEnumerable<TOtherIteratorKey, TOther> $other
	 * @param null|callable(TSource, TOther): TResult $resultSelector
	 * @param null|callable(TIteratorKey, TOtherIteratorKey): TResultKey $keySelector
	 *
	 * @return GenericEnumerable<TResultKey, TResult>
	 */
	public function zip(GenericEnumerable $other, ?callable $resultSelector = null, ?callable $keySelector = null): GenericEnumerable
	{
		$resultSelector ??= static fn (mixed $a, mixed $b): array => [$a, $b];
		/** @psalm-suppress UnusedClosureParam */
		$keySelector ??= static fn (mixed $a, mixed $b): mixed => $a;

		$mit = new MultipleIterator(MultipleIterator::MIT_KEYS_NUMERIC | MultipleIterator::MIT_NEED_ALL);
		$mit->attachIterator($this->getIterator());
		$mit->attachIterator($other->getIterator());
		/** @var MultipleIterator<array{TIteratorKey, TOtherIteratorKey}, array{TSource, TOther}> $mit */

		/** @var GenericEnumerable<TResultKey, TResult> */
		return new Enumerable(
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
