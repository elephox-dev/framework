<?php
declare(strict_types=1);

namespace Elephox\Collection;

use JetBrains\PhpStorm\Pure;
use Elephox\Support\Contract\ArrayConvertible;

/**
 * @template TKey as array-key
 * @template TValue
 *
 * @template-implements Contract\GenericMap<TKey, TValue>
 * @template-implements ArrayConvertible<TKey, TValue>
 */
class ArrayMap implements Contract\GenericMap, ArrayConvertible
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
}
