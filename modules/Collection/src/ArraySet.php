<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Closure;
use Elephox\Collection\Contract\GenericSet;
use Iterator;
use IteratorIterator;
use Traversable;

/**
 * @template T
 *
 * @implements GenericSet<T>
 */
class ArraySet implements GenericSet
{
	// TODO: replace generic enumerable function with array-specific functions where possible
	/**
	 * @use IsEnumerable<T>
	 */
	use IsEnumerable {
		IsEnumerable::count as genericCount;
	}

	/**
	 * @use IsArrayEnumerable<array-key, T>
	 */
	use IsArrayEnumerable {
		IsArrayEnumerable::contains as arrayContains;
		IsArrayEnumerable::count as arrayCount;
	}

	/**
	 * @var Closure(null|T, null|T): bool
	 */
	private readonly Closure $comparer;

	/**
	 * @param array<mixed, T> $items
	 * @param null|Closure(null|T, null|T): bool $comparer
	 */
	public function __construct(
		protected array $items = [],
		?Closure $comparer = null,
	) {
		$this->comparer = $comparer ?? DefaultEqualityComparer::same(...);
	}

	/**
	 * @return Traversable<array-key, T>
	 */
	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->items);
	}

	public function add(mixed $value): bool
	{
		if ($this->contains($value)) {
			return false;
		}

		$this->items[] = $value;

		return true;
	}

	public function addAll(iterable $values): bool
	{
		$allAdded = true;

		foreach ($values as $value) {
			$allAdded = $this->add($value) && $allAdded;
		}

		return $allAdded;
	}

	public function remove(mixed $value): bool
	{
		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}
		/** @var Iterator $iterator */
		$iterator->rewind();
		while ($iterator->valid()) {
			/** @var T $currentValue */
			$currentValue = $iterator->current();
			if (($this->comparer)($value, $currentValue)) {
				/** @var array-key $key */
				$key = $iterator->key();
				unset($this->items[$key]);

				return true;
			}

			$iterator->next();
		}

		return false;
	}

	public function removeBy(callable $predicate): bool
	{
		$iterator = $this->getIterator();
		if (!($iterator instanceof Iterator)) {
			$iterator = new IteratorIterator($iterator);
		}
		/** @var Iterator $iterator */
		$iterator->rewind();
		$anyRemoved = false;
		while ($iterator->valid()) {
			/** @var T $value */
			$value = $iterator->current();
			if ($predicate($value)) {
				/** @var array-key $key */
				$key = $iterator->key();
				unset($this->items[$key]);

				$anyRemoved = true;
			}

			$iterator->next();
		}

		return $anyRemoved;
	}

	public function removeAll(): bool
	{
		$anyRemoved = count($this->items) > 0;

		$this->items = [];

		return $anyRemoved;
	}

	public function contains(mixed $value, ?callable $comparer = null): bool
	{
		return $this->arrayContains($value, $comparer ?? $this->comparer);
	}

	public function count(?callable $predicate = null): int {
		if ($predicate === null) {
			return $this->arrayCount();
		}

		return $this->genericCount($predicate);
	}
}
