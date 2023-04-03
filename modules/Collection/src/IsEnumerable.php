<?php
/** @noinspection PhpUnhandledExceptionInspection */
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
use Elephox\Collection\Iterator\GroupingIterator;
use Elephox\Collection\Iterator\KeySelectIterator;
use Elephox\Collection\Iterator\OrderedIterator;
use Elephox\Collection\Iterator\ReverseIterator;
use Elephox\Collection\Iterator\SelectIterator;
use Elephox\Collection\Iterator\UniqueByIterator;
use Elephox\Collection\Iterator\WhileIterator;
use EmptyIterator;
use Generator;
use InvalidArgumentException;
use Iterator;
use IteratorIterator;
use JetBrains\PhpStorm\ExpectedValues;
use JsonException;
use LimitIterator;
use MultipleIterator as ParallelIterator;
use NoRewindIterator;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Traversable;

use const JSON_FORCE_OBJECT;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_INVALID_UTF8_IGNORE;
use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_NUMERIC_CHECK;
use const JSON_PARTIAL_OUTPUT_ON_ERROR;
use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_LINE_TERMINATORS;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const JSON_THROW_ON_ERROR;

/**
 * @psalm-type NonNegativeInteger = int<0,max>
 *
 * @template TSource
 */
trait IsEnumerable
{
	// FIXME: de-duplicate code from IsEnumerable and IsKeyedEnumerable where possible (move iterator creation to trait and return self with created iterator)

	/**
	 * @return Traversable<mixed, TSource>
	 */
	abstract public function getIterator(): Traversable;

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

	public function any(?callable $predicate = null): bool
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

	public function appendAll(iterable $values): GenericEnumerable
	{
		return new Enumerable(function () use ($values) {
			yield from $this->getIterator();
			yield from $values;
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
	public function count(?callable $predicate = null): int
	{
		$iterator = $this->getIterator();
		if ($predicate !== null) {
			if (!($iterator instanceof Iterator)) {
				$iterator = new IteratorIterator($iterator);
			}
			$iterator = new CallbackFilterIterator($iterator, $predicate);
		} elseif ($iterator instanceof Countable) {
			/** @var NonNegativeInteger */
			return $iterator->count();
		}

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
	 * @return GenericEnumerable<TSource>
	 */
	public function distinctBy(callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		/**
		 * @var Closure(TSource, TSource): bool $comparer
		 * @var Closure(TSource): TSource $keySelector
		 */
		return new Enumerable(new UniqueByIterator($iterator, $keySelector(...), $comparer(...)));
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

		return $this->exceptBy($other, static fn (mixed $element): mixed => $element, $comparer);
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
	 * @return GenericEnumerable<TSource>
	 */
	public function flatten(): GenericEnumerable {
		return new Enumerable(function() {
			$it = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->getIterator()));
			foreach($it as $v) {
				yield $v;
			}
		});
	}

	/**
	 * @param callable(TSource): void $callback
	 *
	 * @return void
	 */
	public function forEach(callable $callback): void
	{
		foreach ($this->getIterator() as $element) {
			$callback($element);
		}
	}

	/**
	 * @template TGroupKey
	 *
	 * @param callable(TSource): TGroupKey $keySelector
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericGroupedKeyedEnumerable<TGroupKey, mixed, TSource>
	 */
	public function groupBy(callable $keySelector, ?callable $comparer = null): GenericGroupedKeyedEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		return new GroupedKeyedEnumerable(new GroupingIterator($this->getIterator(), $keySelector(...), $comparer(...)));
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

		return $this->intersectBy($other, static fn ($element): mixed => $element, $comparer);
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

	public function isEmpty(): bool
	{
		return $this->count() === 0;
	}

	public function isNotEmpty(): bool
	{
		return !$this->isEmpty();
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
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}
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
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}
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

		return new OrderedEnumerable(new OrderedIterator($this->getIterator(), $keySelector(...), $comparer(...)));
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
		$comparer = DefaultEqualityComparer::invert($comparer);
		/** @var callable(mixed, mixed): int $comparer */

		return new OrderedEnumerable(new OrderedIterator($this->getIterator(), $keySelector(...), $comparer(...)));
	}

	public function prepend(mixed $value): GenericEnumerable
	{
		return new Enumerable(function () use ($value) {
			yield $value;

			yield from $this->getIterator();
		});
	}

	public function prependAll(iterable $values): GenericEnumerable
	{
		return new Enumerable(function () use ($values) {
			yield from $values;

			yield from $this->getIterator();
		});
	}

	public function reverse(bool $preserveKeys = true): GenericEnumerable
	{
		return new Enumerable(new ReverseIterator($this->getIterator(), $preserveKeys));
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
		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		return new Enumerable(new SelectIterator($iterator, $selector(...)));
	}

	/**
	 * @template TCollection
	 * @template TIntermediateKey
	 * @template TCollectionKey
	 * @template TResult
	 *
	 * @param callable(TSource): GenericKeyedEnumerable<TIntermediateKey, TCollection> $collectionSelector
	 * @param null|callable(TSource, TCollection, TIntermediateKey): TResult $resultSelector
	 * @param null|callable(TSource, TCollection, TIntermediateKey): TCollectionKey $keySelector
	 *
	 * @return GenericKeyedEnumerable<TCollectionKey, TResult>
	 */
	public function selectManyKeyed(callable $collectionSelector, ?callable $resultSelector = null, ?callable $keySelector = null): GenericKeyedEnumerable
	{
		$resultSelector ??= static fn (mixed $element, mixed $collectionElement, mixed $collectionElementKey): mixed => $collectionElement;
		$keySelector ??= static fn (mixed $element, mixed $collectionElement, mixed $collectionElementKey): mixed => $collectionElementKey;
		/** @var callable(TSource, TCollection, TIntermediateKey): TCollectionKey $keySelector */

		return new KeyedEnumerable(function () use ($collectionSelector, $resultSelector, $keySelector) {
			foreach ($this->getIterator() as $element) {
				foreach ($collectionSelector($element) as $collectionElementKey => $collectionElement) {
					yield $keySelector($element, $collectionElement, $collectionElementKey) => $resultSelector($element, $collectionElement, $collectionElementKey);
				}
			}
		});
	}

	/**
	 * @template TCollection
	 * @template TResult
	 *
	 * @param callable(TSource): GenericEnumerable<TCollection> $collectionSelector
	 * @param null|callable(TSource, TCollection): TResult $resultSelector
	 *
	 * @return GenericEnumerable<TResult>
	 */
	public function selectMany(callable $collectionSelector, ?callable $resultSelector = null): GenericEnumerable
	{
		$resultSelector ??= static fn (mixed $element, mixed $collectionElement): mixed => $collectionElement;
		/** @var callable(TSource, TCollection): TResult $resultSelector */

		return new Enumerable(function () use ($collectionSelector, $resultSelector) {
			/** @var TSource $element */
			foreach ($this->getIterator() as $element) {
				foreach ($collectionSelector($element) as $collectionElement) {
					yield $resultSelector($element, $collectionElement);
				}
			}
		});
	}

	public function sequenceEqual(GenericEnumerable $other, ?callable $comparer = null): bool
	{
		$comparer ??= DefaultEqualityComparer::same(...);
		/** @var callable(TSource, TSource): bool $comparer */
		$otherIterator = $other->getIterator();
		if (!($otherIterator instanceof Iterator)) {
			$otherIterator = new IteratorIterator($otherIterator);
		}
		/** @var Iterator<mixed, TSource> $otherIterator */
		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		$mit = new ParallelIterator(ParallelIterator::MIT_KEYS_NUMERIC | ParallelIterator::MIT_NEED_ANY);
		$mit->attachIterator($iterator);
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
		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		return new Enumerable(new LimitIterator($iterator, $count));
	}

	public function skipLast(int $count): GenericEnumerable
	{
		if ($count <= 0) {
			throw new InvalidArgumentException('Count must be greater than zero');
		}

		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}
		$cachedIterator = new CachingIterator($iterator, CachingIterator::FULL_CACHE);
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
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

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
		return $this->aggregate(static function (mixed $accumulator, mixed $element) use ($selector) {
			/**
			 * @var numeric $accumulator
			 * @var TSource $element
			 */
			return $accumulator + $selector($element);
		}, 0);
	}

	public function take(int $count): GenericEnumerable
	{
		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		return new Enumerable(new LimitIterator($iterator, 0, $count));
	}

	public function takeLast(int $count): GenericEnumerable
	{
		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}
		$cachedIterator = new CachingIterator($iterator, CachingIterator::FULL_CACHE);
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
		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		return new Enumerable(new WhileIterator($iterator, $predicate(...)));
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
	 * @return ArrayList<TSource>
	 */
	public function toArrayList(): ArrayList
	{
		return new ArrayList($this->toList());
	}

	/**
	 * @throws JsonException
	 */
	public function toJson(
		#[ExpectedValues(flags: [
			JSON_FORCE_OBJECT,
			JSON_HEX_QUOT,
			JSON_HEX_TAG,
			JSON_HEX_AMP,
			JSON_HEX_APOS,
			JSON_INVALID_UTF8_IGNORE,
			JSON_INVALID_UTF8_SUBSTITUTE,
			JSON_NUMERIC_CHECK,
			JSON_PARTIAL_OUTPUT_ON_ERROR,
			JSON_PRESERVE_ZERO_FRACTION,
			JSON_PRETTY_PRINT,
			JSON_UNESCAPED_LINE_TERMINATORS,
			JSON_UNESCAPED_SLASHES,
			JSON_UNESCAPED_UNICODE,
			JSON_THROW_ON_ERROR,
		])] int $flags = 0,
		int $depth = 512,
	): string {
		return json_encode($this->toList(), $flags | JSON_THROW_ON_ERROR, $depth);
	}

	/**
	 * @template USource
	 * @template UKey
	 *
	 * @param Iterator<UKey, USource> $iterator
	 *
	 * @return GenericKeyedEnumerable<NonNegativeInteger, USource>
	 */
	private static function reindex(Iterator $iterator): GenericKeyedEnumerable
	{
		$key = 0;

		return new KeyedEnumerable(new KeySelectIterator($iterator, static function () use (&$key): int {
			/**
			 * @var NonNegativeInteger $key
			 */
			return $key++;
		}));
	}

	/**
	 * @template TArrayKey as array-key
	 *
	 * @param null|callable(NonNegativeInteger, TSource): TArrayKey $keySelector
	 *
	 * @return array<TArrayKey, TSource>
	 */
	public function toArray(?callable $keySelector = null): array
	{
		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		return self::reindex($iterator)->toArray($keySelector);
	}

	/**
	 * @template TArrayKey as array-key
	 *
	 * @param null|callable(NonNegativeInteger, TSource): TArrayKey $keySelector
	 *
	 * @return array<TArrayKey, list<TSource>>
	 */
	public function toNestedArray(?callable $keySelector = null): array
	{
		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		return self::reindex($iterator)->groupByKey($keySelector);
	}

	public function toKeyed(callable $keySelector): GenericKeyedEnumerable
	{
		$valueProxy = static fn (mixed $key, mixed $value): mixed => $keySelector($value);

		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		return new KeyedEnumerable(new KeySelectIterator($iterator, $valueProxy(...)));
	}

	/**
	 * @param GenericEnumerable<TSource> $other
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function union(GenericEnumerable $other, ?callable $comparer = null): GenericEnumerable
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
	 * @param GenericEnumerable<TSource> $other
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function unionBy(GenericEnumerable $other, callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		$otherIterator = $other->getIterator();
		if (!($otherIterator instanceof Iterator)) {
			$otherIterator = new IteratorIterator($otherIterator);
		}

		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		$append = new AppendIterator();
		$append->append($iterator);
		$append->append($otherIterator);

		/**
		 * @var Closure(TSource): TCompareKey $keySelector
		 * @var Closure(TCompareKey, TCompareKey): bool $comparer
		 */
		return new Enumerable(new UniqueByIterator($append, $keySelector(...), $comparer(...)));
	}

	/**
	 * @param null|callable(TSource, TSource): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function unique(?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);
		$identity = static fn (mixed $o): mixed => $o;

		/**
		 * @var callable(TSource, TSource): bool $comparer
		 * @var callable(TSource): TSource $identity
		 */
		return $this->uniqueBy($identity, $comparer);
	}

	/**
	 * @template TCompareKey
	 *
	 * @param callable(TSource): TCompareKey $keySelector
	 * @param null|callable(TCompareKey, TCompareKey): bool $comparer
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function uniqueBy(callable $keySelector, ?callable $comparer = null): GenericEnumerable
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		/**
		 * @var Closure(TSource): TCompareKey $keySelector
		 * @var Closure(TCompareKey, TCompareKey): bool $comparer
		 */
		return new Enumerable(new UniqueByIterator($iterator, $keySelector(...), $comparer(...)));
	}

	/**
	 * @param callable(TSource): bool $predicate
	 *
	 * @return GenericEnumerable<TSource>
	 */
	public function where(callable $predicate): GenericEnumerable
	{
		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		/** @var Iterator<mixed, TSource> $iterator */
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
		$resultSelector ??= static fn (mixed $a, mixed $b): array => [$a, $b];

		$otherIterator = $other->getIterator();
		if (!($otherIterator instanceof Iterator)) {
			$otherIterator = new IteratorIterator($otherIterator);
		}

		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}

		$mit = new ParallelIterator(ParallelIterator::MIT_KEYS_NUMERIC | ParallelIterator::MIT_NEED_ALL);
		$mit->attachIterator($iterator);
		$mit->attachIterator($otherIterator);
		/** @var ParallelIterator $mit */

		/** @var GenericEnumerable<TResult> */
		return new Enumerable(
			/** @var SelectIterator<mixed, TResult> */
			new SelectIterator(
				$mit,
				static function (mixed $values) use ($resultSelector): array {
					/** @var array{TSource, TOther} $values */
					return $resultSelector($values[0], $values[1]);
				},
			),
		);
	}
}
