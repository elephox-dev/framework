<?php

namespace Philly\Collection;

/**
 * @template TKey as array-key
 * @template TValue
 *
 * @template-implements Contract\GenericMap<TKey, TValue>
 */
class ArrayMap implements Contract\GenericMap
{
	/** @var array<TKey, TValue> */
	protected array $values = [];

	/**
	 * @param iterable<TKey, TValue> $items
	 */
	public function __construct(iterable $items = [])
	{
		foreach ($items as $key => $value) {
			$this->put($key, $value);
		}
	}

	public function put(mixed $key, mixed $value): void
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_int($key) && !is_string($key)) {
			throw new OffsetNotAllowedException($key);
		}

		$this->values[$key] = $value;
	}

	public function get(mixed $key): mixed
	{
		if (!array_key_exists($key, $this->values)) {
			throw new OffsetNotFoundException($key);
		}

		return $this->values[$key];
	}

	public function first(callable $filter): mixed
	{
		foreach ($this->values as $key => $value) {
			if ($filter($value, $key)) {
				return $value;
			}
		}

		return null;
	}

	public function where(callable $filter): ArrayMap
	{
		$result = new ArrayMap();

		foreach ($this->values as $key => $item) {
			if ($filter($item)) {
				$result->put($key, $item);
			}
		}

		return $result;
	}

	public function has(mixed $key): bool
	{
		return array_key_exists($key, $this->values);
	}

	/**
	 * @template TOut
	 *
	 * @param callable(TValue, TKey): TOut $callback
	 * @return ArrayMap<TKey, TOut>
	 */
	public function map(callable $callback): ArrayMap
	{
		$map = new ArrayMap();

		foreach ($this->values as $key => $value) {
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
