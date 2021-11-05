<?php

namespace Philly\Collection;

use JetBrains\PhpStorm\Pure;

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

	#[Pure] public function first(?callable $filter = null): mixed
	{
		foreach ($this->values as $key => $value) {
			if ($filter === null || $filter($value, $key)) {
				return $value;
			}
		}

		return null;
	}

	#[Pure] public function where(callable $filter): ArrayMap
	{
		$result = new ArrayMap();

		foreach ($this->values as $key => $item) {
			if ($filter($item)) {
				/** @psalm-suppress ImpureMethodCall Since this call is on another instance of ArrayMap. */
				$result->put($key, $item);
			}
		}

		return $result;
	}

	#[Pure] public function has(mixed $key): bool
	{
		return array_key_exists($key, $this->values);
	}

	/**
	 * @template TOut
	 *
	 * @param callable(TValue, TKey): TOut $callback
	 * @return ArrayMap<TKey, TOut>
	 */
	#[Pure] public function map(callable $callback): ArrayMap
	{
		$map = new ArrayMap();

		foreach ($this->values as $key => $value) {
			/**
			 * @psalm-suppress InvalidArgument Until vimeo/psalm#6821 is fixed
			 * @psalm-suppress ImpureMethodCall Since this call is on another instance of ArrayMap.
			 */
			$map->put($key, $callback($value, $key));
		}

		return $map;
	}

	#[Pure] public function any(?callable $filter = null): bool
	{
		return $this->first($filter) !== null;
	}
}