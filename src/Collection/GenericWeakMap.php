<?php

namespace Philly\Collection;

use Philly\Collection\Contract\GenericMap;
use WeakMap;

/**
 * @template TValue
 *
 * @template-implements GenericMap<object, TValue>
 */
class GenericWeakMap implements GenericMap
{
	private WeakMap $map;

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
			throw new InvalidOffsetException($key);
		}

		/** @var TValue */
		return $this->map->offsetGet($key);
	}

	public function first(callable $filter): mixed
	{
		/**
		 * @var TValue $value
		 */
		foreach ($this->map as $value) {
			if ($filter($value)) {
				return $value;
			}
		}

		return null;
	}

	/**
	 * @param callable(TValue, object): bool $filter
	 * @return GenericWeakMap<TValue>
	 */
	public function where(callable $filter): GenericWeakMap
	{
		$result = new GenericWeakMap();

		/**
		 * @var object $key
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
	 * @return object|null
	 */
	public function firstKey(callable $filter): mixed
	{
		/**
		 * @var object $key
		 * @var TValue $value
		 */
		foreach ($this->map as $key => $value) {
			if ($filter($key, $value)) {
				return $key;
			}
		}

		return null;
	}

	public function whereKey(callable $filter): GenericWeakMap
	{
		$result = new GenericWeakMap();

		/**
		 * @var object $key
		 * @var TValue $value
		 */
		foreach ($this->map as $key => $value) {
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
}
