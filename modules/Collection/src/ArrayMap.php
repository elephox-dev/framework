<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Support\Contract\ArrayConvertible;
use Elephox\Support\Contract\JsonConvertible;
use JetBrains\PhpStorm\Pure;

/**
 * @template TKey as array-key
 * @template TValue
 *
 * @template-implements Contract\GenericMap<TKey, TValue>
 * @template-implements ArrayConvertible<TKey, TValue>
 */
class ArrayMap implements Contract\GenericMap, ArrayConvertible, JsonConvertible
{
	/**
	 * @template TPairKey as array-key
	 * @template TPairValue
	 *
	 * @param ArrayList<KeyValuePair<TPairKey, TPairValue>> $list
	 * @return ArrayMap<TPairKey, TPairValue>
	 */
	public static function fromKeyValuePairList(ArrayList $list): self
	{
		$map = new ArrayMap();

		foreach ($list as $keyValuePair) {
			$key = $keyValuePair->getKey();
			if ($map->has($key)) {
				throw new DuplicateKeyException($key);
			}

			$map->put($key, $keyValuePair->getValue());
		}

		return $map;
	}

	/**
	 * @template TMapKey as array-key
	 * @template TMapValue
	 *
	 * @param iterable<TMapKey, TMapValue> $map
	 *
	 * @returns ArrayMap<TMapKey, TMapValue>
	 */
	public static function fromIterable(iterable $map): self
	{
		return new self($map);
	}

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

	#[Pure] public function firstKey(?callable $filter = null): mixed
	{
		foreach ($this->values as $key => $value) {
			if ($filter === null || $filter($key, $value)) {
				return $key;
			}
		}

		return null;
	}

	/**
	 * @param callable(TValue, TKey): bool $filter
	 * @return ArrayMap<TKey, TValue>
	 */
	public function where(callable $filter): ArrayMap
	{
		$result = new ArrayMap();

		foreach ($this->values as $key => $item) {
			if ($filter($item, $key)) {
				$result->put($key, $item);
			}
		}

		return $result;
	}

	/**
	 * @param callable(TKey, TValue): bool $filter
	 * @return ArrayMap<TKey, TValue>
	 */
	public function whereKey(callable $filter): ArrayMap
	{
		$result = new ArrayMap();

		foreach ($this->values as $key => $item) {
			if ($filter($key, $item)) {
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
	public function map(callable $callback): ArrayMap
	{
		$map = new ArrayMap();

		foreach ($this->values as $key => $value) {
			/**
			 * @psalm-suppress InvalidArgument Until vimeo/psalm#6821 is fixed
			 */
			$map->put($key, $callback($value, $key));
		}

		return $map;
	}

	#[Pure] public function any(?callable $filter = null): bool
	{
		return $this->first($filter) !== null;
	}

	#[Pure] public function anyKey(?callable $filter = null): bool
	{
		return $this->firstKey($filter) !== null;
	}

	public function values(): ArrayList
	{
		return new ArrayList(array_values($this->values));
	}

	public function keys(): ArrayList
	{
		return new ArrayList(array_keys($this->values));
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

		foreach ($this->values as $key => $value) {
			$list->add($callback($value, $key));
		}

		return $list;
	}

	public function asArray(): array
	{
		return $this->values;
	}

	#[Pure] public function contains(mixed $value): bool
	{
		return $this->any(static fn($item) => $item === $value);
	}

	/**
	 * @return ArrayIterator<TKey, TValue>
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->values);
	}

	public function toJson(int $flags = JSON_THROW_ON_ERROR): string
	{
		return json_encode($this->values, JSON_THROW_ON_ERROR);
	}

	/**
	 * @template TKeyOut of array-key
	 *
	 * @param callable(TKey, TValue): TKeyOut $callback
	 * @return ArrayMap<TKeyOut, TValue>
	 */
	public function mapKeys(callable $callback): ArrayMap
	{
		/** @var ArrayMap<TKeyOut, TValue> $map */
		$map = new ArrayMap();

		foreach ($this->values as $key => $value) {
			/** @psalm-suppress InvalidArgument Until vimeo/psalm#6821 is fixed */
			$map->put($callback($key, $value), $value);
		}

		return $map;
	}
}
