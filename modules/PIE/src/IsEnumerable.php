<?php
declare(strict_types=1);

namespace Elephox\PIE;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

/**
 * @template TSource
 * @template TIteratorKey
 *
 * @psalm-require-implements GenericEnumerable<TSource, TIteratorKey>
 */
trait IsEnumerable
{
	/**
	 * @return GenericIterator<TSource, TIteratorKey>
	 */
	abstract public function getIterator(): GenericIterator;

	#[Pure] public function aggregate(callable $accumulator, mixed $seed = null, callable $resultSelector = null): mixed
	{
		$result = $seed;

		foreach ($this->getIterator() as $key => $element) {
			$result = $accumulator($result, $element, $key);
		}

		return $resultSelector ? $resultSelector($result) : $result;
	}

	#[Pure] public function all(callable $predicate): bool
	{
		foreach ($this->getIterator() as $element) {
			if (!$predicate($element)) {
				return false;
			}
		}

		return true;
	}

	#[Pure] public function any(callable $predicate = null): bool
	{
		foreach ($this->getIterator() as $key => $element) {
			if ($predicate === null || $predicate($element, $key)) {
				return true;
			}
		}

		return false;
	}

	#[Pure] public function append(mixed $value): GenericEnumerable
	{
		return new Enumerable(new AppendIterator($this->getIterator(), new SingleElementIterator($value)));
	}

	#[Pure] public function average(callable $selector): int|float
	{
		$sum = null;
		$count = 0;

		foreach ($this->getIterator() as $key => $element) {
			$value = $selector($element, $key);
			if (!is_numeric($value)) {
				throw new InvalidArgumentException('The average selector must return a numeric value.');
			}

			if ($sum === null) {
				$sum = $value;
			} else {
				$sum += $value;
			}

			$count++;
		}

		return $sum / $count;
	}

	#[Pure] public function chunk(int $size): GenericEnumerable
	{
		return new Enumerable(function () use ($size) {
			$index = 0;
			$chunk = [];
			foreach ($this->getIterator() as $element) {
				if ($index % $size === 0) {
					yield $chunk;

					$chunk = [];
				} else {
					$chunk[] = $element;
				}
			}

			if ($chunk) {
				yield $chunk;
			}
		});
	}

	#[Pure] public function concat(GenericEnumerable ...$enumerables): GenericEnumerable
	{
		return new Enumerable(function () use ($enumerables) {
			yield from $this;

			foreach ($enumerables as $enumerable) {
				yield from $enumerable;
			}
		});
	}

	#[Pure] public function contains(mixed $value, ?callable $comparer = null): bool
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		foreach ($this->getIterator() as $element) {
			if ($comparer($value, $element)) {
				return true;
			}
		}

		return false;
	}

	#[Pure] public function count(callable $predicate = null): int
	{
		$count = 0;

		foreach ($this->getIterator() as $key => $element) {
			if ($predicate === null || $predicate($element, $key)) {
				$count++;
			}
		}

		return $count;
	}

	#[Pure] public function defaultIfEmpty(mixed $defaultValue = null): GenericEnumerable
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

	#[Pure] public function distinct(?callable $comparer = null): GenericEnumerable
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

	#[Pure] public function distinctBy(callable $keySelector, ?callable $comparer = null): GenericEnumerable
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

	#[Pure] public function elementAt(int $index): mixed
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

	#[Pure] public function elementAtOrDefault(int $index, mixed $defaultValue): mixed
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

	#[Pure] public function except(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new Enumerable(function () use ($other, $comparer) {
			$others = $other->toList();
			foreach ($this->getIterator() as $key => $element) {
				foreach ($others as $otherElement) {
					if ($comparer($element, $otherElement)) {
						continue 2;
					}
				}

				yield $key => $element;
			}
		});
	}

	#[Pure] public function exceptBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new Enumerable(function () use ($other, $keySelector, $comparer) {
			$otherKeys = [];
			foreach ($other->getIterator() as $otherElement) {
				$otherKeys[] = $keySelector($otherElement);
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

	#[Pure] public function first(?callable $predicate = null): mixed
	{
		foreach ($this->getIterator() as $element) {
			if ($predicate === null || $predicate($element)) {
				return $element;
			}
		}

		throw new InvalidArgumentException('Sequence contains no matching element');
	}

	#[Pure] public function firstOrDefault(mixed $defaultValue, ?callable $predicate = null): mixed
	{
		foreach ($this->getIterator() as $element) {
			if ($predicate === null || $predicate($element)) {
				return $element;
			}
		}

		return $defaultValue;
	}

//	#[Pure] public function groupBy(callable $keySelector, ?callable $elementSelector = null, ?callable $resultSelector = null, ?callable $comparer = null): GenericEnumerable
//	{
//		$comparer ??= DefaultEqualityComparer::same(...);
//
//		// TODO: Implement groupBy() method.
//	}
//
//	#[Pure] public function groupJoin(GenericEnumerable $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): GenericEnumerable
//	{
//		$comparer ??= DefaultEqualityComparer::same(...);
//
//		// TODO: Implement groupJoin() method.
//	}

	#[Pure] public function intersect(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new Enumerable(function () use ($other, $comparer) {
			$others = $other->toList();
			foreach ($this->getIterator() as $elementKey => $element) {
				foreach ($others as $otherElement) {
					if ($comparer($element, $otherElement)) {
						yield $elementKey => $element;

						continue 2;
					}
				}
			}
		});
	}

	#[Pure] public function intersectBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new Enumerable(function () use ($other, $keySelector, $comparer) {
			$otherKeys = [];
			foreach ($other->getIterator() as $otherElement) {
				$otherKeys[] = $keySelector($otherElement);
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

	#[Pure] public function join(GenericEnumerable $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): GenericEnumerable
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

	#[Pure] public function last(?callable $predicate = null): mixed
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

	#[Pure] public function lastOrDefault(mixed $default, ?callable $predicate = null): mixed
	{
		$last = null;
		foreach ($this->getIterator() as $element) {
			if ($predicate === null || $predicate($element)) {
				$last = $element;
			}
		}

		return $last ?? $default;
	}

	#[Pure] public function max(callable $selector): int|float
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

	#[Pure] public function min(callable $selector): int|float
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

	#[Pure] public function orderBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
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

	#[Pure] public function orderByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::compare(...);

		return $this->orderBy($keySelector, DefaultEqualityComparer::invert($comparer));
	}

	#[Pure] public function prepend(mixed $value): GenericEnumerable
	{
		return new Enumerable(function () use ($value) {
			yield $value;

			yield from $this->getIterator();
		});
	}

	#[Pure] public function reverse(): GenericEnumerable
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

	#[Pure] public function selectMany(callable $collectionSelector, callable $resultSelector): GenericEnumerable
	{
		return new Enumerable(function () use ($collectionSelector, $resultSelector) {
			foreach ($this->getIterator() as $elementKey => $element) {
				foreach ($collectionSelector($element, $elementKey) as $collectionElementKey => $collectionElement) {
					yield $resultSelector($element, $collectionElement, $elementKey, $collectionElementKey);
				}
			}
		});
	}

	#[Pure] public function sequenceEqual(GenericEnumerable $other, ?callable $comparer = null): bool
	{
		$comparer ??= DefaultEqualityComparer::same(...);
		$others = $other->toList();

		foreach ($this->getIterator() as $element) {
			foreach ($others as $otherElement) {
				if (!$comparer($element, $otherElement)) {
					return false;
				}
			}
		}

		return true;
	}

	#[Pure] public function single(?callable $predicate = null): mixed
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

	#[Pure] public function singleOrDefault(mixed $default, ?callable $predicate = null): mixed
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

	#[Pure] public function skip(int $count): GenericEnumerable
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

	#[Pure] public function skipLast(int $count): GenericEnumerable
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

	#[Pure] public function skipWhile(callable $predicate): GenericEnumerable
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

	#[Pure] public function sum(callable $selector): int|float
	{
		$sum = null;

		foreach ($this->getIterator() as $element) {
			$value = $selector($element);
			if (!is_numeric($value)) {
				throw new InvalidArgumentException('The average selector must return a numeric value.');
			}

			if ($sum === null) {
				$sum = $value;
			} else {
				$sum += $value;
			}
		}

		return $sum;
	}

	/**
	 * @param callable(TSource): bool $selector
	 *
	 * @return array{int|float, int}
	 */
	#[Pure] protected function sumAndCount(callable $selector): array
	{
		$sum = null;
		$count = 0;

		foreach ($this->getIterator() as $element) {
			$value = $selector($element);
			if (!is_numeric($value)) {
				throw new InvalidArgumentException('The average selector must return a numeric value.');
			}

			if ($sum === null) {
				$sum = $value;
			} else {
				$sum += $value;
			}

			$count++;
		}

		return [$sum, $count];
	}

	#[Pure] public function take(int $count): GenericEnumerable
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

	#[Pure] public function takeLast(int $count): GenericEnumerable
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

	#[Pure] public function takeWhile(callable $predicate): GenericEnumerable
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

	#[Pure] public function toList(): array
	{
		return iterator_to_array($this->getIterator(), false);
	}

	#[Pure] public function toArray(): array
	{
		return iterator_to_array($this->getIterator());
	}

	#[Pure] public function union(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::compare(...);

		return $this->unionBy($other, static fn (mixed $o) => $o, $comparer);
	}

	#[Pure] public function unionBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::compare(...);

		return new Enumerable(function () use ($other, $keySelector, $comparer) {
			$iterator = $this->getIterator();
			$otherIterator = $other->getIterator();

			while ($iterator->valid() && $otherIterator->valid()) {
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

	#[Pure] public function where(callable $predicate): GenericEnumerable
	{
		return new Enumerable(function () use ($predicate) {
			foreach ($this->getIterator() as $element) {
				if ($predicate($element)) {
					yield $element;
				}
			}
		});
	}

	#[Pure] public function zip(GenericEnumerable $other, ?callable $resultSelector = null): GenericEnumerable
	{
		$resultSelector ??= static fn (mixed $a, mixed $b) => [$a, $b];

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
