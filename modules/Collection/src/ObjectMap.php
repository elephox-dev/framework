<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\ReadonlyMap;
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
	public function get(mixed $key): mixed
	{
		if (!$this->map->offsetExists($key)) {
			throw new OffsetNotFoundException($key);
		}

		/** @var TValue */
		return $this->map->offsetGet($key);
	}

	public function first(?callable $filter = null): mixed
	{
		/** @var TKey $key */
		foreach ($this->map as $key) {
			/** @var TValue $value */
			$value = $this->map->offsetGet($key);
			if ($filter === null || $filter($value, $key)) {
				return $value;
			}
		}

		return null;
	}

	public function firstKey(?callable $filter = null): mixed
	{
		/** @var TKey $key */
		foreach ($this->map as $key) {
			/** @var TValue $value */
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
	public function where(callable $filter): ObjectMap
	{
		/** @var ObjectMap<TKey, TValue> $result */
		$result = new ObjectMap();

		/** @var TKey $key */
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
	public function whereKey(callable $filter): ObjectMap
	{
		/** @var ObjectMap<TKey, TValue> $result */
		$result = new ObjectMap();

		/** @var TKey $key */
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
	public function has(mixed $key): bool
	{
		return $this->map->offsetExists($key);
	}

	/**
	 * @template TOut
	 *
	 * @param callable(TValue, TKey): TOut $callback
	 * @return ObjectMap<TKey, TOut>
	 */
	public function map(callable $callback): ObjectMap
	{
		/** @var ObjectMap<TKey, TOut> $map */
		$map = new ObjectMap();

		/** @var TKey $key */
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
	public function mapKeys(callable $callback): ObjectMap
	{
		/** @var ObjectMap<TKeyOut, TValue> $map */
		$map = new ObjectMap();

		/** @var TKey $key */
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
	public function reduce(callable $callback): ArrayList
	{
		/** @var ArrayList<TOut> $list */
		$list = new ArrayList();

		/** @var TKey $key */
		foreach ($this->map as $key) {
			/** @var TValue $value */
			$value = $this->map->offsetGet($key);

			$list->add($callback($value, $key));
		}

		return $list;
	}

	public function any(?callable $filter = null): bool
	{
		return $this->first($filter) !== null;
	}

	public function anyKey(?callable $filter = null): bool
	{
		return $this->firstKey($filter) !== null;
	}

	public function values(): ArrayList
	{
		/** @var ArrayList<TValue> $list */
		$list = new ArrayList();

		/** @var TKey $key */
		foreach ($this->map as $key) {
			/** @var TValue $item */
			$item = $this->map->offsetGet($key);

			$list->add($item);
		}

		return $list;
	}

	public function keys(): ArrayList
	{
		/** @var ArrayList<TKey> $list */
		$list = new ArrayList();

		/** @var TKey $key */
		foreach ($this->map as $key) {
			$list->add($key);
		}

		return $list;
	}

	public function contains(mixed $value): bool
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

	public function asReadonly(): ReadonlyMap
	{
		return $this;
	}

	public function remove(mixed $key): void
	{
		$this->map->offsetUnset($key);
	}
}
