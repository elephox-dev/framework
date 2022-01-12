<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Contract\GenericList;
use Elephox\Support\DeepCloneable;
use InvalidArgumentException;
use Iterator;

/**
 * @template T
 *
 * @implements GenericList<T>
 */
class ArrayList implements GenericList
{
	/**
	 * @uses IsKeyedEnumerable<int, T>
	 */
	use IsKeyedEnumerable;
	use DeepCloneable;

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
			return new self($value);
		}

		if ($value instanceof Iterator) {
			return new self(iterator_to_array($value));
		}

		return new self([$value]);
	}

	public function __construct(
		private array $items = []
	) {
	}

	public function getIterator(): Iterator
	{
		return new ArrayIterator($this->items);
	}

	public function offsetExists(mixed $offset): bool
	{
		if (!is_int($offset)) {
			throw new OffsetNotAllowedException($offset);
		}

		return $offset < $this->count();
	}

	public function offsetGet(mixed $offset): mixed
	{
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

		if (!is_int($offset)) {
			throw new OffsetNotAllowedException($offset);
		}

		$this->put($offset, $value);
	}

	public function offsetUnset(mixed $offset): void
	{
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
				return $index;
			}
		}

		return -1;
	}

	public function lastIndexOf(mixed $value, ?callable $comparer = null): int
	{
		$comparer ??= DefaultEqualityComparer::same(...);

		$index = -1;

		foreach ($this->items as $index => $item) {
			if ($comparer($item, $value)) {
				return $index;
			}
		}

		return $index;
	}
}
