<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Closure;
use Elephox\Collection\Contract\GenericSet;
use Elephox\Support\DeepCloneable;

/**
 * @template T
 *
 * @implements GenericSet<T>
 */
class ArraySet implements GenericSet
{
	/**
	 * @use IsEnumerable<T>
	 */
	use IsEnumerable {
		IsEnumerable::contains as enumerableContains;
	}
	use DeepCloneable;

	/**
	 * @var Closure(null|T, null|T): bool
	 */
	private readonly Closure $comparer;

	/**
	 * @param array<mixed, T> $items
	 * @param null|Closure(null|T, null|T): bool $comparer
	 */
	public function __construct(
		private array $items = [],
		?Closure $comparer = null,
	) {
		$this->comparer = $comparer ?? DefaultEqualityComparer::same(...);
	}

	public function getIterator(): ArrayIterator
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

	public function remove(mixed $value): bool
	{
		$iterator = $this->getIterator();
		$iterator->rewind();

		while ($iterator->valid()) {
			if (($this->comparer)($value, $iterator->current())) {
				/** @psalm-suppress PossiblyNullArrayOffset */
				unset($this->items[$iterator->key()]);

				return true;
			}

			$iterator->next();
		}

		return false;
	}

	public function removeBy(callable $predicate): bool
	{
		$iterator = $this->getIterator();
		$iterator->rewind();

		$anyRemoved = false;
		while ($iterator->valid()) {
			/** @psalm-suppress PossiblyNullArgument */
			if ($predicate($iterator->current())) {
				/** @psalm-suppress PossiblyNullArrayOffset */
				unset($this->items[$iterator->key()]);

				$anyRemoved = true;
			}

			$iterator->next();
		}

		return $anyRemoved;
	}

	public function contains(mixed $value, ?callable $comparer = null): bool
	{
		return $this->enumerableContains($value, $comparer ?? $this->comparer);
	}
}
