<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Iterator;
use Elephox\Collection\Contract\GenericList;
use JetBrains\PhpStorm\Pure;
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
	 * @param array<array-key, TKey> $keys
	 * @param array<array-key, TValue> $values
	 */
	public function __construct(array $keys = [], array $values = [])
	{
		$this->map = new WeakMap();

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

	#[Pure] public function first(?callable $filter = null): mixed
	{
		/**
		 * @var TKey $key
		 * @var TValue $value
		 */
		foreach ($this->map as $key => $value) {
			if ($filter === null || $filter($value, $key)) {
				return $value;
			}
		}

		return null;
	}

	/**
	 * @param callable(TValue, TKey): bool $filter
	 * @return GenericWeakMap<TKey, TValue>
	 */
	#[Pure] public function where(callable $filter): GenericWeakMap
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
	#[Pure] public function map(callable $callback): GenericWeakMap
	{
		/** @var GenericWeakMap<TKey, TOut> $map */
		$map = new GenericWeakMap();

		/**
		 * @var TKey $key
		 * @var TValue $value
		 */
		foreach ($this->map as $key => $value) {
			/** @psalm-suppress InvalidArgument Until vimeo/psalm#6821 is fixed */
			$map->put($key, $callback($value, $key));
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
		 * @var TKey $key
		 * @var TValue $value
		 */
		foreach ($this->map as $key => $value) {
			$list->add($callback($value, $key));
		}

		return $list;
	}

	#[Pure] public function any(?callable $filter = null): bool
	{
		return $this->first($filter) !== null;
	}

	public function values(): GenericList
	{
		/** @var ArrayList<TValue> $list */
		$list = new ArrayList();

		/** @var TValue $item */
		foreach ($this->map as $item) {
			$list->add($item);
		}

		return $list;
	}

	public function keys(): GenericList
	{
		/** @var ArrayList<TKey> $list */
		$list = new ArrayList();

		/** @var Iterator $iterator */
		$iterator = $this->map->getIterator();
		while ($iterator->valid()) {
			/** @var TKey $key */
			$key = $iterator->key();
			$list->add($key);
			$iterator->next();
		}

		return $list;
	}

	#[Pure] public function contains(mixed $value): bool
	{
		return $this->any(static fn($item) => $item === $value);
	}
}
