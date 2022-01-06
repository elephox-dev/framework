<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Closure;
use Elephox\Collection\Contract\GenericCollection;
use Elephox\Collection\Contract\GenericSet;
use Elephox\PIE\DefaultEqualityComparer;
use Elephox\Support\DeepCloneable;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use SplObjectStorage;

/**
 * @template T
 *
 * @implements GenericSet<T>
 */
class ObjectSet implements GenericSet
{
	use DeepCloneable;

	/** @var SplObjectStorage<object, mixed> */
	protected SplObjectStorage $storage;

	public function __construct(
		private ?Closure $comparer = null
	) {
		$this->storage = new SplObjectStorage();
		$this->comparer ??= DefaultEqualityComparer::same(...);
	}

	public function getIterator(): SplObjectStorageIterator
	{
		return new SplObjectStorageIterator($this->storage);
	}

	#[Pure] public function first(?callable $filter = null): mixed
	{
		// TODO: Implement first() method.
	}

	#[Pure] public function any(?callable $filter = null): bool
	{
		// TODO: Implement any() method.
	}

	#[Pure] public function where(callable $filter): GenericCollection
	{
		// TODO: Implement where() method.
	}

	public function contains(mixed $value): bool
	{
		if (!is_object($value)) {
			throw new InvalidArgumentException("Cannot add non-object to " . $this::class);
		}

		return $this->storage->contains($value);
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

	public function map(callable $callback): GenericCollection
	{
		// TODO: Implement map() method.
	}
}
