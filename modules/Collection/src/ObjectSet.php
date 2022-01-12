<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Closure;
use Elephox\Collection\Contract\GenericSet;
use Elephox\Collection\Iterator\SplObjectStorageIterator;
use Elephox\Support\DeepCloneable;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use SplObjectStorage;

/**
 * @template T of object
 *
 * @implements GenericSet<T>
 */
class ObjectSet implements GenericSet
{
	/**
	 * @uses IsEnumerable<T>
	 */
	use IsEnumerable {
		contains as enumerableContains;
	}
	use DeepCloneable;

	/** @var SplObjectStorage<T, mixed> */
	private SplObjectStorage $storage;

	/**
	 * @var Closure(T, T): bool
	 */
	private Closure $comparer;

	/**
	 * @param null|Closure(T, T): bool $comparer
	 */
	#[Pure] public function __construct(
		?Closure $comparer = null
	) {
		$this->storage = new SplObjectStorage();
		$this->comparer = $comparer ?? DefaultEqualityComparer::same(...);
	}

	#[Pure] public function getIterator(): SplObjectStorageIterator
	{
		return new SplObjectStorageIterator($this->storage);
	}

	public function add(mixed $value): bool
	{
		if (!is_object($value)) {
			throw new InvalidArgumentException("Cannot add non-object to " . $this::class);
		}

		$existed = $this->contains($value);

		$this->storage->attach($value);

		return !$existed;
	}

	public function remove(mixed $value): bool
	{
		if (!is_object($value)) {
			throw new InvalidArgumentException("Cannot add non-object to " . $this::class);
		}

		$existed = $this->contains($value);

		$this->storage->detach($value);

		return $existed;
	}

	public function removeBy(callable $predicate): bool
	{
		$anyRemoved = false;

		foreach ($this->getIterator() as $object) {
			if ($predicate($object)) {
				$this->storage->detach($object);

				$anyRemoved = true;
			}
		}

		return $anyRemoved;
	}

	public function contains(mixed $value, ?callable $comparer = null): bool
	{
		return $this->enumerableContains($value, $comparer ?? $this->comparer);
	}
}
