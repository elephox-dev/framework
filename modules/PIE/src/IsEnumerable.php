<?php
/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */
declare(strict_types=1);

namespace Elephox\PIE;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Traversable;

/**
 * @template T
 * @template TIteratorKey
 *
 * @psalm-require-implements GenericEnumerable<T, TIteratorKey>
 */
trait IsEnumerable
{
	/**
	 * @return GenericIterator<T, TIteratorKey>
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

	#[Pure] public function groupBy(callable $keySelector, ?callable $elementSelector = null, ?callable $resultSelector = null, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		// TODO: Implement groupBy() method.
	}

	#[Pure] public function groupJoin(GenericEnumerable $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		// TODO: Implement groupJoin() method.
	}

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

		// TODO: Implement join() method.
	}

	#[Pure] public function last(?callable $predicate = null): mixed
	{
		// TODO: Implement last() method.
	}

	#[Pure] public function lastOrDefault(mixed $default, ?callable $predicate = null): mixed
	{
		// TODO: Implement lastOrDefault() method.
	}

	#[Pure] public function max(callable $selector): int|float
	{
		// TODO: Implement max() method.
	}

	#[Pure] public function min(callable $selector): int|float
	{
		// TODO: Implement min() method.
	}

	#[Pure] public function ofType(string $type): GenericEnumerable
	{
		// TODO: Implement ofType() method.
	}

	#[Pure] public function orderBy(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		// TODO: Implement orderBy() method.
	}

	#[Pure] public function orderByDescending(callable $keySelector, ?callable $comparer = null): GenericOrderedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		// TODO: Implement orderByDescending() method.
	}

	#[Pure] public function prepend(mixed $value): GenericEnumerable
	{
		// TODO: Implement prepend() method.
	}

	#[Pure] public function reverse(): GenericEnumerable
	{
		// TODO: Implement reverse() method.
	}

	public function select(callable $selector): GenericEnumerable
	{
		// TODO: Implement select() method.
	}

	#[Pure] public function selectMany(callable $collectionSelector, callable $resultSelector): GenericEnumerable
	{
		// TODO: Implement selectMany() method.
	}

	#[Pure] public function sequenceEqual(GenericEnumerable $other, ?callable $comparer = null): bool
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		// TODO: Implement sequenceEqual() method.
	}

	#[Pure] public function single(?callable $predicate = null): mixed
	{
		// TODO: Implement single() method.
	}

	#[Pure] public function singleOrDefault(mixed $default, ?callable $predicate = null): mixed
	{
		// TODO: Implement singleOrDefault() method.
	}

	#[Pure] public function skip(int $count): GenericEnumerable
	{
		// TODO: Implement skip() method.
	}

	#[Pure] public function skipLast(int $count): GenericEnumerable
	{
		// TODO: Implement skipLast() method.
	}

	#[Pure] public function skipWhile(callable $predicate): GenericEnumerable
	{
		// TODO: Implement skipWhile() method.
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
	 * @param callable(T): bool $selector
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
		// TODO: Implement takeWhile() method.
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
		$comparer ??= DefaultEqualityComparer::same(...);

		// TODO: Implement union() method.
	}

	#[Pure] public function unionBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		// TODO: Implement unionBy() method.
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

	#[Pure] public function zip(GenericEnumerable $second, ?callable $resultSelector = null): GenericEnumerable
	{
		// TODO: Implement zip() method.
	}
}
