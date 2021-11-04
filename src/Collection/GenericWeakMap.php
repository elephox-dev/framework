<?php

namespace Philly\Collection;

use WeakMap;

/**
 * @template TKey as object
 * @template TValue
 *
 * @template-implements Contract\GenericMap<TKey, TValue>
 */
class GenericWeakMap implements Contract\GenericMap
{
	protected WeakMap $map;

	/**
	 * @param iterable<object, TValue> $items
	 */
	public function __construct(iterable $items = [])
	{
		$this->map = new WeakMap();

		foreach ($items as $key => $value) {
			$this->put($key, $value);
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

	public function first(callable $filter): mixed
	{
		/**
		 * @var TKey $key
		 * @var TValue $value
		 */
		foreach ($this->map as $key => $value) {
			if ($filter($value, $key)) {
				return $value;
			}
		}

		return null;
	}

	/**
	 * @param callable(TValue, TKey): bool $filter
	 * @return GenericWeakMap<TKey, TValue>
	 */
	public function where(callable $filter): GenericWeakMap
	{
		/** @var GenericWeakMap<TKey, TValue> $result */
		$result = new GenericWeakMap();

		/**
		 * @var TKey $key
		 * @var TValue $value
		 */
		foreach ($this->map as $key => $value) {
			if ($filter($value, $key)) {
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
	 * @return GenericWeakMap<TKey, TOut>
	 */
	public function map(callable $callback): GenericWeakMap
	{
		/** @var GenericWeakMap<TKey, TOut> $map */
		$map = new GenericWeakMap();

		/**
		 * @var object $key
		 * @var TValue $value
		 */
		foreach ($this->map as $key => $value) {
			/** @psalm-suppress InvalidArgument Until vimeo/psalm#6821 is fixed */
			$map->put($key, $callback($value, $key));
		}

		return $map;
	}

	public function any(callable $filter): bool
	{
		return $this->first($filter) !== null;
	}
}
