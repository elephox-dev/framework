<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayAccess;
use ArrayIterator;
use Elephox\Collection\Contract\ReadonlyMap;
use Elephox\Support\Contract\ArrayConvertible;
use Elephox\Support\Contract\JsonConvertible;
use Elephox\Support\DeepCloneable;
use JetBrains\PhpStorm\Pure;

/**
 * @template TKey as array-key
 * @template TValue
 *
 * @template-implements Contract\GenericMap<TKey, TValue>
 * @template-implements ArrayConvertible<TKey, TValue>
 * @template-implements ArrayAccess<TKey, TValue>
 */
class ArrayMap implements Contract\GenericMap, ArrayAccess, ArrayConvertible, JsonConvertible
{
	use DeepCloneable;

	/**
	 * @template TPairKey as array-key
	 * @template TPairValue
	 *
	 * @param list<KeyValuePair<TPairKey, TPairValue>> $list
	 * @return ArrayMap<TPairKey, TPairValue>
	 */
	#[Pure] public static function fromKeyValuePairList(iterable $list): self
	{
		$map = new ArrayMap();

		foreach ($list as $keyValuePair) {
			$key = $keyValuePair->getKey();
			if ($map->has($key)) {
				throw new DuplicateKeyException($key);
			}

			/** @psalm-suppress ImpureMethodCall */
			$map->put($key, $keyValuePair->getValue());
		}

		return $map;
	}

	/**
	 * @template TMapKey as array-key
	 * @template TMapValue
	 *
	 * @param array<TMapKey, TMapValue> $map
	 *
	 * @returns ArrayMap<TMapKey, TMapValue>
	 */
	#[Pure] public static function fromIterable(iterable $map): self
	{
		return new self($map);
	}

	/** @var array<TKey, TValue> */
	protected array $values = [];

	/**
	 * @param array<TKey, TValue> $items
	 */
	#[Pure] public function __construct(iterable $items = [])
	{
		$this->values = $items;
	}

	public function put(mixed $key, mixed $value): void
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_int($key) && !is_string($key)) {
			throw new OffsetNotAllowedException($key);
		}

		$this->values[$key] = $value;
	}

	#[Pure] public function get(mixed $key): mixed
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
				/** @var TKey|null */
				return $key;
			}
		}

		return null;
	}

	/**
	 * @param callable(TValue, TKey): bool $filter
	 * @return ArrayMap<TKey, TValue>
	 */
	#[Pure] public function where(callable $filter): ArrayMap
	{
		$result = new ArrayMap();

		foreach ($this->values as $key => $item) {
			if ($filter($item, $key)) {
				/** @psalm-suppress ImpureMethodCall */
				$result->put($key, $item);
			}
		}

		return $result;
	}

	/**
	 * @param callable(TKey, TValue): bool $filter
	 * @return ArrayMap<TKey, TValue>
	 */
	#[Pure] public function whereKey(callable $filter): ArrayMap
	{
		$result = new ArrayMap();

		foreach ($this->values as $key => $item) {
			if ($filter($key, $item)) {
				/** @psalm-suppress ImpureMethodCall */
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
			 * @psalm-suppress ImpureMethodCall
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

	#[Pure] public function values(): ArrayList
	{
		return new ArrayList(array_values($this->values));
	}

	#[Pure] public function keys(): ArrayList
	{
		return new ArrayList(array_keys($this->values));
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

		foreach ($this->values as $key => $value) {
			/** @psalm-suppress ImpureMethodCall */
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

	public function toJson(int $flags = 0): string
	{
		return json_encode($this->values, $flags | JSON_THROW_ON_ERROR);
	}

	/**
	 * @template TKeyOut of array-key
	 *
	 * @param callable(TKey, TValue): TKeyOut $callback
	 * @return ArrayMap<TKeyOut, TValue>
	 */
	#[Pure] public function mapKeys(callable $callback): ArrayMap
	{
		/** @var ArrayMap<TKeyOut, TValue> $map */
		$map = new ArrayMap();

		foreach ($this->values as $key => $value) {
			/**
			 * @psalm-suppress ImpureMethodCall
			 * @psalm-suppress InvalidArgument Until vimeo/psalm#6821 is fixed
			 */
			$map->put($callback($key, $value), $value);
		}

		return $map;
	}

	#[Pure] public function asReadonly(): ReadonlyMap
	{
		return $this;
	}

	public function remove(mixed $key): void
	{
		unset($this->values[$key]);
	}

	#[Pure] public function offsetExists(mixed $offset): bool
	{
		return $this->has($offset);
	}

	#[Pure] public function offsetGet(mixed $offset): mixed
	{
		return $this->get($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->put($offset, $value);
	}

	public function offsetUnset(mixed $offset): void
	{
		$this->remove($offset);
	}
}
