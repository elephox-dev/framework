<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\ReadonlyMap;
use Elephox\Support\DeepCloneable;
use JetBrains\PhpStorm\Pure;
use SplObjectStorage;
use Traversable;

/**
 * @template TKey as object
 * @template TValue
 *
 * @template-implements Contract\GenericMap<TKey, TValue>
 */
class ObjectMap implements Contract\GenericMap
{
	use DeepCloneable;

	protected SplObjectStorage $map;

	/**
	 * @param array<array-key, TKey> $keys
	 * @param array<array-key, TValue> $values
	 */
	public function __construct(array $keys = [], array $values = [])
	{
		$this->map = new SplObjectStorage();

		foreach ($keys as $index => $key) {
			if (!array_key_exists($index, $values)) {
				throw new OffsetNotFoundException($index);
			}

			$this->put($key, $values[$index]);
		}
	}

	/**
	 * @param object $key
	 * @param TValue $value
	 */
	public function put(mixed $key, mixed $value): void
	{
		$this->map->offsetSet($key, $value);
	}

	/**
	 * @param object $key
	 * @return TValue
	 */
	#[Pure] public function get(mixed $key): mixed
	{
		/** @psalm-suppress ImpureMethodCall */
		if (!$this->map->offsetExists($key)) {
			throw new OffsetNotFoundException($key);
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var TValue
		 */
		return $this->map->offsetGet($key);
	}

	#[Pure] public function first(?callable $filter = null): mixed
	{
		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var TKey $key
		 */
		foreach ($this->map as $key) {
			/** @var TValue $value */
			$value = $this->map->offsetGet($key);
			if ($filter === null || $filter($value, $key)) {
				return $value;
			}
		}

		return null;
	}

	#[Pure] public function firstKey(?callable $filter = null): mixed
	{
		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var TKey $key
		 */
		foreach ($this->map as $key) {
			/**
			 * @psalm-suppress ImpureMethodCall
			 * @var TValue $value
			 */
			$value = $this->map->offsetGet($key);
			if ($filter === null || $filter($key, $value)) {
				return $key;
			}
		}

		return null;
	}

	/**
	 * @param callable(TValue, TKey): bool $filter
	 * @return ObjectMap<TKey, TValue>
	 */
	#[Pure] public function where(callable $filter): ObjectMap
	{
		/** @var ObjectMap<TKey, TValue> $result */
		$result = new ObjectMap();

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var TKey $key
		 */
		foreach ($this->map as $key) {
			/** @var TValue $value */
			$value = $this->map->offsetGet($key);
			if ($filter($value, $key)) {
				$result->put($key, $value);
			}
		}

		return $result;
	}

	/**
	 * @param callable(TKey, TValue): bool $filter
	 * @return ObjectMap<TKey, TValue>
	 */
	#[Pure] public function whereKey(callable $filter): ObjectMap
	{
		/** @var ObjectMap<TKey, TValue> $result */
		$result = new ObjectMap();

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var TKey $key
		 */
		foreach ($this->map as $key) {
			/** @var TValue $value */
			$value = $this->map->offsetGet($key);
			if ($filter($key, $value)) {
				$result->put($key, $value);
			}
		}

		return $result;
	}

	/**
	 * @param object $key
	 *
	 * @return bool
	 */
	#[Pure] public function has(mixed $key): bool
	{
		/** @psalm-suppress ImpureMethodCall */
		return $this->map->offsetExists($key);
	}

	/**
	 * @template TOut
	 *
	 * @param callable(TValue, TKey): TOut $callback
	 * @return ObjectMap<TKey, TOut>
	 */
	#[Pure] public function map(callable $callback): ObjectMap
	{
		/** @var ObjectMap<TKey, TOut> $map */
		$map = new ObjectMap();

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var TKey $key
		 */
		foreach ($this->map as $key) {
			/** @var TValue $value */
			$value = $this->map->offsetGet($key);

			/** @psalm-suppress InvalidArgument Until vimeo/psalm#6821 is fixed */
			$map->put($key, $callback($value, $key));
		}

		return $map;
	}

	/**
	 * @template TKeyOut of object
	 *
	 * @param callable(TKey, TValue): TKeyOut $callback
	 * @return ObjectMap<TKeyOut, TValue>
	 */
	#[Pure] public function mapKeys(callable $callback): ObjectMap
	{
		/** @var ObjectMap<TKeyOut, TValue> $map */
		$map = new ObjectMap();

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var TKey $key
		 */
		foreach ($this->map as $key) {
			/** @var TValue $value */
			$value = $this->map->offsetGet($key);

			/** @psalm-suppress InvalidArgument Until vimeo/psalm#6821 is fixed */
			$map->put($callback($key, $value), $value);
		}

		return $map;
	}

	/**
	 * @template TOut
	 *
	 * @param callable(TValue, TKey): TOut $callback
	 * @return ArrayList<TOut>
	 */
	#[Pure] public function reduce(callable $callback): ArrayList
	{
		/** @var ArrayList<TOut> $list */
		$list = new ArrayList();

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var TKey $key
		 */
		foreach ($this->map as $key) {
			/** @var TValue $value */
			$value = $this->map->offsetGet($key);

			$list->add($callback($value, $key));
		}

		return $list;
	}

	#[Pure] public function any(?callable $filter = null): bool
	{
		return $this->first($filter) !== null;
	}

	#[Pure] public function anyKey(?callable $filter = null): bool
	{
		return $this->firstKey($filter) !== null;
	}

	#[Pure] public function values(): ArrayList
	{
		/** @var ArrayList<TValue> $list */
		$list = new ArrayList();

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var TKey $key
		 */
		foreach ($this->map as $key) {
			/** @var TValue $item */
			$item = $this->map->offsetGet($key);

			$list->add($item);
		}

		return $list;
	}

	#[Pure] public function keys(): ArrayList
	{
		/** @var ArrayList<TKey> $list */
		$list = new ArrayList();

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var TKey $key
		 */
		foreach ($this->map as $key) {
			$list->add($key);
		}

		return $list;
	}

	#[Pure] public function contains(mixed $value): bool
	{
		return $this->any(static fn($item) => $item === $value);
	}

	/**
	 * @return Traversable<TKey, TValue>
	 */
	public function getIterator(): Traversable
	{
		/** @var TKey $key */
		foreach ($this->map as $key) {
			/** @var TValue $value */
			$value = $this->map->offsetGet($key);

			yield $key => $value;
		}
	}

	#[Pure] public function asReadonly(): ReadonlyMap
	{
		return $this;
	}

	public function remove(mixed $key): void
	{
		$this->map->offsetUnset($key);
	}

	public function __clone()
	{
		$this->map = clone $this->map;
	}
}
