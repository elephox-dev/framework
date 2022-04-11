<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Contract\GenericList;
use InvalidArgumentException;
use Iterator;

/**
 * @template T
 *
 * @implements GenericList<T>
 */
class ArrayList implements GenericList
{
	// TODO: replace generic enumerable function with array-specific functions where possible
	/**
	 * @use IsKeyedEnumerable<int, T>
	 */
	use IsKeyedEnumerable {
		IsKeyedEnumerable::contains as genericContains;
		IsKeyedEnumerable::firstOrDefault as genericFirstOrDefault;
	}

	/**
	 * @use IsArrayEnumerable<int, T>
	 */
	use IsArrayEnumerable {
		IsArrayEnumerable::contains as arrayContains;
		IsArrayEnumerable::firstOrDefault as arrayFirstOrDefault;
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

	public function getIterator(): Iterator
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
		/** @psalm-suppress DocblockTypeContradiction */
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

	public function remove(mixed $value, ?callable $comparer = null): bool
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

	public function removeAt(int $index): mixed
	{
		if ($index < 0 || $index >= $this->count()) {
			throw new OffsetNotFoundException($index);
		}

		$removed = $this->items[$index];

		array_splice($this->items, $index, 1);

		return $removed;
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

		foreach ($this->items as $index => $item) {
			if ($comparer($item, $value)) {
				/** @var int $index */
				return $index;
			}
		}

		return -1;
	}

	/**
	 * @param null|callable(T): bool $predicate
	 *
	 * @return T
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

		$value = $this->firstOrDefault(null, $predicate);
		if ($value === null) {
			throw new EmptySequenceException();
		}

		$this->remove($value);

		return $value;
	}

	/**
	 * @return T
	 */
	public function shift(): mixed
	{
		if ($this->count() === 0) {
			throw new EmptySequenceException();
		}

		/** @var T */
		return array_shift($this->items);
	}

	/**
	 * @param T $value
	 */
	public function unshift(mixed $value): void
	{
		array_unshift($this->items, $value);
	}

	public function implode(string $separator = ', '): string
	{
		return implode($separator, array_map(static fn (mixed $v) => (string) $v, $this->items));
	}

	public function contains(mixed $value, ?callable $comparer = null): bool
	{
		return $this->arrayContains($value, $comparer);
	}

	/**
	 * @template TDefault
	 *
	 * @param TDefault $defaultValue
	 * @param null|callable(T, int): bool $predicate
	 *
	 * @return TDefault|T
	 */
	public function firstOrDefault(mixed $defaultValue, ?callable $predicate = null): mixed
	{
		/** @var T|TDefault */
		return $this->genericFirstOrDefault($defaultValue, $predicate);
	}
}
