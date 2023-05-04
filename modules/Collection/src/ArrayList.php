<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Contract\GenericArrayList;
use InvalidArgumentException;
use Iterator;
use Traversable;

/**
 * @template T
 *
 * @implements GenericArrayList<T>
 */
class ArrayList implements GenericArrayList
{
	// TODO: replace generic enumerable function with array-specific functions where possible
	/**
	 * @use IsKeyedEnumerable<int, T>
	 */
	use IsKeyedEnumerable {
		IsKeyedEnumerable::count as genericCount;
	}

	/**
	 * @use IsArrayEnumerable<int, T>
	 */
	use IsArrayEnumerable {
		IsArrayEnumerable::contains as arrayContains;
		IsArrayEnumerable::containsKey as arrayContainsKey;
		IsArrayEnumerable::count as arrayCount;
	}

	/**
	 * @template UValue
	 *
	 * @param ArrayList<UValue>|iterable<UValue>|UValue $value
	 *
	 * @return ArrayList<UValue>
	 */
	public static function from(mixed $value): self
	{
		if ($value instanceof self) {
			return $value;
		}

		if (is_array($value)) {
			if (!array_is_list($value)) {
				throw new InvalidArgumentException('ArrayList::from() expects a list of values');
			}

			return new self($value);
		}

		if ($value instanceof Iterator) {
			return new self(array_values(iterator_to_array($value)));
		}

		/** @var list<UValue> $value */
		$value = [$value];

		return new self($value);
	}

	/**
	 * @param array<int, T> $items
	 */
	public function __construct(
		protected array $items = [],
	) {
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->items);
	}

	public function offsetExists(mixed $offset): bool
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_int($offset)) {
			throw new OffsetNotAllowedException($offset);
		}

		return $offset < $this->count();
	}

	public function offsetGet(mixed $offset): mixed
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_int($offset)) {
			throw new OffsetNotAllowedException($offset);
		}

		return $this->elementAt($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if ($offset === null) {
			$this->add($value);

			return;
		}

		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_int($offset)) {
			throw new OffsetNotAllowedException($offset);
		}

		$this->put($offset, $value);
	}

	public function offsetUnset(mixed $offset): void
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_int($offset)) {
			throw new OffsetNotAllowedException($offset);
		}

		$this->removeAt($offset);
	}

	public function add(mixed $value): bool
	{
		$this->items[] = $value;

		return true;
	}

	public function addAll(iterable $values): bool
	{
		$added = false;

		foreach ($values as $value) {
			$added = $this->add($value) || $added;
		}

		return $added;
	}

	public function put(int $index, mixed $value): bool
	{
		if ($index < 0 || $index > $this->count()) {
			throw new OffsetNotAllowedException($index);
		}

		$this->items[$index] = $value;

		return true;
	}

	public function removeValue(mixed $value, ?callable $comparer = null): bool
	{
		$index = $this->indexOf($value, $comparer);

		if ($index === -1) {
			return false;
		}

		$this->removeAt($index);

		return true;
	}

	public function elementAt(int $index): mixed
	{
		if ($index < 0 || $index >= $this->count()) {
			throw new OffsetNotFoundException($index);
		}

		/** @var T */
		return $this->items[$index];
	}

	/**
	 * @return T
	 *
	 * @param int $index
	 */
	public function removeAt(int $index): mixed
	{
		if ($index < 0 || $index >= $this->count()) {
			throw new OffsetNotFoundException($index);
		}

		$removed = $this->items[$index];

		array_splice($this->items, $index, 1);

		return $removed;
	}

	public function clear(): void
	{
		$this->items = [];
	}

	public function indexOf(mixed $value, ?callable $comparer = null): int
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		foreach ($this->items as $index => $item) {
			if ($comparer($item, $value)) {
				/** @var int */
				return $index;
			}
		}

		return -1;
	}

	public function lastIndexOf(mixed $value, ?callable $comparer = null): int
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		$lastMatchingIndex = -1;
		foreach ($this->items as $index => $item) {
			if ($comparer($item, $value)) {
				$lastMatchingIndex = $index;
			}
		}

		return $lastMatchingIndex;
	}

	/**
	 * @param null|callable(T, int): bool $predicate
	 *
	 * @return T
	 *
	 * @throws EmptySequenceException
	 */
	public function pop(?callable $predicate = null): mixed
	{
		if ($this->count() === 0) {
			throw new EmptySequenceException();
		}

		if ($predicate === null) {
			/** @var T */
			return array_pop($this->items);
		}

		/** @var null|int $key */
		$key = $this->reverse()->firstKeyOrDefault(null, $predicate);
		if ($key === null) {
			throw new EmptySequenceException();
		}

		return $this->removeAt($key);
	}

	/**
	 * @param null|callable(T, int): bool $predicate
	 *
	 * @return T
	 *
	 * @throws EmptySequenceException
	 */
	public function shift(?callable $predicate = null): mixed
	{
		if ($this->count() === 0) {
			throw new EmptySequenceException();
		}

		if ($predicate === null) {
			/** @var T */
			return array_shift($this->items);
		}

		$key = $this->firstKeyOrDefault(null, $predicate);
		if ($key === null) {
			throw new EmptySequenceException();
		}

		return $this->removeAt($key);
	}

	/**
	 * @param T $value
	 */
	public function unshift(mixed $value): void
	{
		array_unshift($this->items, $value);
	}

	public function contains(mixed $value, ?callable $comparer = null): bool
	{
		return $this->arrayContains($value, $comparer);
	}

	public function containsKey(mixed $key, ?callable $comparer = null): bool
	{
		return $this->arrayContainsKey($key, $comparer);
	}

	/**
	 * @param T $value
	 */
	public function insertAt(int $index, mixed $value): void
	{
		array_splice($this->items, $index, 0, [$value]);
	}

	/**
	 * @return ArrayList<T>&static
	 */
	public function slice(int $offset, ?int $length = null): static
	{
		/** @var ArrayList<T>&static */
		return new self(array_slice($this->items, $offset, $length));
	}

	public function key(): ?int
	{
		return key($this->items);
	}

	public function count(?callable $predicate = null): int {
		if ($predicate === null) {
			return $this->arrayCount();
		}

		return $this->genericCount($predicate);
	}
}
