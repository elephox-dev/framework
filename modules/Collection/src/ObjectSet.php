<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Closure;
use Elephox\Collection\Contract\GenericSet;
use Elephox\Collection\Iterator\FlipIterator;
use Elephox\Collection\Iterator\SplObjectStorageIterator;
use InvalidArgumentException;
use Iterator;
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
	 * @use IsEnumerable<T>
	 */
	use IsEnumerable {
		IsEnumerable::contains as enumerableContains;
	}

	/**
	 * @var SplObjectStorage<T, mixed>
	 */
	private readonly SplObjectStorage $storage;

	/**
	 * @var Closure(T, T): bool
	 */
	private readonly Closure $comparer;

	/**
	 * @param null|Closure(T, T): bool $comparer
	 */
	#[Pure]
	public function __construct(
		?Closure $comparer = null,
	) {
		$this->storage = new SplObjectStorage();
		$this->comparer = $comparer ?? DefaultEqualityComparer::same(...);
	}

	#[Pure]
	public function getIterator(): Iterator
	{
		/** @var FlipIterator<mixed, object> */
		return new FlipIterator(new SplObjectStorageIterator($this->storage));
	}

	public function add(mixed $value): bool
	{
		if (!is_object($value)) {
			throw new InvalidArgumentException('Cannot add non-object to ' . $this::class);
		}

		$existed = $this->contains($value);

		$this->storage->attach($value);

		return !$existed;
	}

	public function remove(mixed $value): bool
	{
		if (!is_object($value)) {
			throw new InvalidArgumentException('Cannot remove non-object from ' . $this::class);
		}

		$existed = $this->contains($value);

		$this->storage->detach($value);

		return $existed;
	}

	public function removeBy(callable $predicate): bool
	{
		$remove = [];
		foreach ($this->getIterator() as $object) {
			if ($predicate($object)) {
				$remove[] = $object;
			}
		}

		foreach ($remove as $object) {
			$this->storage->detach($object);
		}

		return count($remove) > 0;
	}

	public function contains(mixed $value, ?callable $comparer = null): bool
	{
		return $this->enumerableContains($value, $comparer ?? $this->comparer);
	}
}
