<?php
declare(strict_types=1);

namespace Elephox\PIE;

use InvalidArgumentException;

/**
 * @template TSource
 * @template TIteratorKey
 */
trait IsEnumerable
{
	/**
	 * @return GenericIterator<TSource, TIteratorKey>
	 */
	abstract public function getIterator(): GenericIterator;

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
	 * @param int $size
	 *
	 * @return GenericEnumerable<list<TSource>, int>
	 */
	public function chunk(int $size): GenericEnumerable
	{
		/** @var Enumerable<list<TSource>, int> */
		return new Enumerable(function () use ($size) {
			$index = 0;
			$chunkCount = 0;
			$chunk = [];
			foreach ($this->getIterator() as $element) {
				if ($index % $size === 0) {
					yield $chunkCount => $chunk;

					$chunkCount++;
					$chunk = [];
				} else {
					$chunk[] = $element;
				}
			}

			if ($chunk) {
				yield $chunkCount => $chunk;
			}
		});
	}

	public function concat(GenericEnumerable ...$enumerables): GenericEnumerable
	{
		return new Enumerable(function () use ($enumerables) {
			yield from $this;

			foreach ($enumerables as $enumerable) {
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

	public function count(callable $predicate = null): int
	{
		$count = 0;

		foreach ($this->getIterator() as $key => $element) {
			if ($predicate === null || $predicate($element, $key)) {
				$count++;
			}
		}

		return $count;
	}

	public function defaultIfEmpty(mixed $defaultValue = null): GenericEnumerable
	{
		return new Enumerable(function () use ($defaultValue) {
			$iterator = $this->getIterator();
			$iterator->rewind();

			if ($iterator->valid()) {
				yield from $iterator;
			} else {
				yield $defaultValue;
			}
		});
	}

	public function distinct(?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new Enumerable(function () use ($comparer) {
			$seen = [];

			foreach ($this->getIterator() as $key => $element) {
				foreach ($seen as $seenElement) {
					if ($comparer($element, $seenElement)) {
						continue 2;
					}
				}

				$seen[] = $element;

				yield $key => $element;
			}
		});
	}

	/**
	 * @template TKey
	 *
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TKey, TKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function distinctBy(callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new Enumerable(function () use ($keySelector, $comparer) {
			$seenKeys = [];

			foreach ($this->getIterator() as $elementKey => $element) {
				$key = $keySelector($element, $elementKey);

				foreach ($seenKeys as $seenKey) {
					if ($comparer($key, $seenKey)) {
						continue 2;
					}
				}

				$seenKeys[] = $key;

				yield $elementKey => $element;
			}
		});
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
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function except(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return $this->exceptBy($other, fn (mixed $element): mixed => $element, $comparer);
	}

	/**
	 * @template TKey
	 *
	 * @param GenericEnumerable<TSource, TIteratorKey> $other
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TKey, TKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
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
	 * @return GenericEnumerable<TSource, TIteratorKey>
	 */
	public function intersect(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return $this->intersectBy($other, fn ($element): mixed => $element, $comparer);
	}

	/**
	 * @template TKey
	 *
	 * @param GenericEnumerable<TSource, TIteratorKey> $other
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
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
	 * @param GenericEnumerable<TInner, TInnerIteratorKey> $inner
	 * @param callable(TSource, TIteratorKey): TKey $outerKeySelector
	 * @param callable(TInner, TInnerIteratorKey): TKey $innerKeySelector
	 * @param callable(TSource, TInner, TIteratorKey, TInnerIteratorKey): TResult $resultSelector
	 * @param null|callable(TKey, TKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource, TIteratorKey>
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
		$max = null;
		foreach ($this->getIterator() as $element) {
			$max = max($max, $selector($element));
		}

		if ($max === null) {
			throw new InvalidArgumentException('Sequence contains no elements');
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
		$min = null;
		foreach ($this->getIterator() as $element) {
			$min = min($min, $selector($element));
		}

		if ($min === null) {
			throw new InvalidArgumentException('Sequence contains no elements');
		}

		return $min;
	}

	/**
	 * @template TKey
	 *
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TSource, TSource): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource, TIteratorKey>
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

		$originalKeys = $keys;
		usort($keys, $comparer);

		return new OrderedEnumerable(function () use ($keys, $elements, $originalKeys) {
			foreach ($keys as $key) {
				$originalIndex = array_search($key, $originalKeys, true);

				yield $elements[$originalIndex];
			}
		});
	}

	/**
	 * @template TKey
	 *
	 * @param callable(TSource, TIteratorKey): TKey $keySelector
	 * @param null|callable(TSource, TSource): int $comparer
	 *
	 * @return GenericOrderedEnumerable<TSource, TIteratorKey>
	 */
	public function orderByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::compare(...);

		/** @var callable(TSource, TSource): int $comparer */
		$invertedComparer = DefaultEqualityComparer::invert($comparer);

		/** @var callable(TSource, TSource): int $invertedComparer */
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
		return new Enumerable(function () {
			$iterator = $this->getIterator();
			$stack = [];

			while ($iterator->valid()) {
				$stack[] = $iterator->current();

				$iterator->next();
			}

			while (count($stack) > 0) {
				yield array_pop($stack);
			}
		});
	}

	public function select(callable $selector): GenericEnumerable
	{
		return new Enumerable(function () use ($selector) {
			foreach ($this->getIterator() as $elementKey => $element) {
				yield $elementKey => $selector($element, $elementKey);
			}
		});
	}

	/**
	 * @template TCollection
	 * @template TCollectionKey
	 * @template TResult
	 *
	 * @param callable(TSource, TIteratorKey): GenericEnumerable<TCollection, TCollectionKey> $collectionSelector
	 * @param callable(TSource, TCollection, TIteratorKey, TCollectionKey): TResult $resultSelector
	 *
	 * @return GenericEnumerable<TResult, TCollectionKey>
	 */
	public function selectMany(callable $collectionSelector, callable $resultSelector): GenericEnumerable
	{
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
		$others = $other->toList();

		foreach ($this->getIterator() as $element) {
			/**
			 * @var TSource $otherElement
			 */
			foreach ($others as $otherElement) {
				if (!$comparer($element, $otherElement)) {
					return false;
				}
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
		return new Enumerable(function () use ($count) {
			$iterator = $this->getIterator();

			for ($i = 0; $i < $count; $i++) {
				if (!$iterator->valid()) {
					return;
				}

				$iterator->next();
			}

			while ($iterator->valid()) {
				yield $iterator->key() => $iterator->current();

				$iterator->next();
			}
		});
	}

	public function skipLast(int $count): GenericEnumerable
	{
		return new Enumerable(function () use ($count) {
			$iterator = $this->getIterator();
			$queue = [];
			$keyQueue = [];

			while ($iterator->valid()) {
				$queue[] = $iterator->current();
				$keyQueue[] = $iterator->key();

				$iterator->next();
			}

			if (count($queue) <= $count) {
				return;
			}

			for ($i = 0; $i < count($queue) - $count; $i++) {
				yield $keyQueue[$i] => $queue[$i];
			}
		});
	}

	public function skipWhile(callable $predicate): GenericEnumerable
	{
		return new Enumerable(function () use ($predicate) {
			$iterator = $this->getIterator();
			$skip = true;

			while ($iterator->valid()) {
				if ($skip && !$predicate($iterator->current(), $iterator->key())) {
					$skip = false;
				}

				if (!$skip) {
					yield $iterator->key() => $iterator->current();
				}

				$iterator->next();
			}
		});
	}

	/**
	 * @param callable(TSource): numeric $selector
	 *
	 * @return numeric
	 */
	public function sum(callable $selector): int|float|string
	{
		$sum = null;

		foreach ($this->getIterator() as $element) {
			$value = $selector($element);

			/** @var null|numeric $sum */
			if ($sum === null) {
				$sum = $value;
			} else {
				$sum += $value;
			}
		}

		if ($sum === null) {
			throw new InvalidArgumentException('Sequence contains no elements');
		}

		/** @var numeric $sum */
		return $sum;
	}

	public function take(int $count): GenericEnumerable
	{
		return new Enumerable(function () use ($count) {
			$iterator = $this->getIterator();
			$i = 0;

			while ($i < $count && $iterator->valid()) {
				yield $iterator->key() => $iterator->current();
				$iterator->next();
				$i++;
			}
		});
	}

	public function takeLast(int $count): GenericEnumerable
	{
		return new Enumerable(function () use ($count) {
			$valueBuffer = [];
			$keyBuffer = [];
			foreach ($this->getIterator() as $key => $element) {
				$keyBuffer[] = $key;
				$valueBuffer[] = $element;
			}

			$bufferLength = count($valueBuffer);
			$bufferOffset = max(count($valueBuffer) - $count, 0);

			for ($i = $bufferOffset; $i < $bufferLength - 1; $i++) {
				yield $keyBuffer[$i] => $valueBuffer[$i];
			}
		});
	}

	public function takeWhile(callable $predicate): GenericEnumerable
	{
		return new Enumerable(function () use ($predicate) {
			$iterator = $this->getIterator();

			while ($iterator->valid()) {
				if (!$predicate($iterator->current(), $iterator->key())) {
					break;
				}

				yield $iterator->key() => $iterator->current();
				$iterator->next();
			}
		});
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
		$comparer ??= DefaultEqualityComparer::compare(...);

		return $this->unionBy($other, static fn (mixed $o): mixed => $o, $comparer);
	}

	public function unionBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::compare(...);

		return new Enumerable(function () use ($other, $keySelector, $comparer) {
			$iterator = $this->getIterator();
			$otherIterator = $other->getIterator();

			while ($iterator->valid() && $otherIterator->valid()) {
				/** @var int $compare */
				$compare = $comparer($keySelector($iterator->current(), $iterator->key()), $keySelector($otherIterator->current(), $otherIterator->key()));

				if ($compare === 0) {
					yield $iterator->key() => $iterator->current();
					$iterator->next();
					$otherIterator->next();
				} elseif ($compare < 0) {
					yield $iterator->key() => $iterator->current();
					$iterator->next();
				} else {
					yield $otherIterator->key() => $otherIterator->current();
					$otherIterator->next();
				}
			}

			while ($iterator->valid()) {
				yield $iterator->key() => $iterator->current();
				$iterator->next();
			}

			while ($otherIterator->valid()) {
				yield $otherIterator->key() => $otherIterator->current();
				$otherIterator->next();
			}
		});
	}

	public function where(callable $predicate): GenericEnumerable
	{
		return new Enumerable(function () use ($predicate) {
			foreach ($this->getIterator() as $element) {
				if ($predicate($element)) {
					yield $element;
				}
			}
		});
	}

	/**
	 * @template TOther
	 * @template TOtherIteratorKey
	 * @template TResult
	 *
	 * @param GenericEnumerable<TOther, TOtherIteratorKey> $other
	 * @param null|callable(TSource, TOther, TIteratorKey, TOtherIteratorKey): TResult $resultSelector
	 *
	 * @return GenericEnumerable<TResult, TIteratorKey>
	 */
	public function zip(GenericEnumerable $other, ?callable $resultSelector = null): GenericEnumerable
	{
		$resultSelector ??= static fn (mixed $a, mixed $b): array => [$a, $b];
		/** @var callable(TSource, TOther, TIteratorKey, TOtherIteratorKey): TResult $resultSelector */

		return new Enumerable(function () use ($other, $resultSelector) {
			$iterator = $this->getIterator();
			$otherIterator = $other->getIterator();

			while ($iterator->valid() && $otherIterator->valid()) {
				yield $resultSelector($iterator->current(), $otherIterator->current(), $iterator->key(), $otherIterator->key());
				$iterator->next();
				$otherIterator->next();
			}
		});
	}
}
