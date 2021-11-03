<?php

namespace Philly\Collection;

use InvalidArgumentException;
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

	private function typeSafeKey(mixed $key): object
	{
		if (is_object($key)) {
			return $key;
		}

		throw new InvalidArgumentException("Cannot use non-object as key.");
	}

	/**
	 * @param TValue $value
	 */
	public function put(mixed $key, mixed $value): void
	{
		$this->map->offsetSet($this->typeSafeKey($key), $value);
	}

	public function get(mixed $key): mixed
	{
		if (!$this->map->offsetExists($this->typeSafeKey($key))) {
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

	public function where(callable $filter): GenericWeakMap
	{
		$result = new GenericWeakMap();

		/**
		 * @var object $key
		 * @var TValue $value
		 */
		foreach ($this->map as $key => $value) {
			if ($filter($value)) {
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

	public function has(mixed $key, bool $safe = true): bool
	{
		return $this->map->offsetExists($this->typeSafeKey($key));
	}
}
