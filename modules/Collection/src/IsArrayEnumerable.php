<?php
declare(strict_types=1);

namespace Elephox\Collection;

use LogicException;

/**
 * @template TKey of array-key
 * @template TValue
 */
trait IsArrayEnumerable
{
	/**
	 * @var array<TKey, TValue>
	 */
	protected array $items;

	/**
	 * @param TValue $value
	 * @param null|callable(TValue, TValue): bool $comparer
	 */
	public function contains(mixed $value, ?callable $comparer = null): bool
	{
		if ($comparer === null) {
			return in_array($value, $this->items, true);
		}

		foreach ($this->items as $v) {
			if ($comparer($v, $value)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param TKey $key
	 * @param null|callable(TKey, TKey): bool $comparer
	 */
	public function containsKey(mixed $key, ?callable $comparer = null): bool
	{
		if (!is_scalar($key)) {
			throw new LogicException('Only scalar keys are supported');
		}

		if ($comparer === null) {
			/** @var array-key $key */
			return array_key_exists($key, $this->items);
		}

		/** @var TKey $k */
		foreach ($this->items as $k => $v) {
			if ($comparer($key, $k)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return TValue|false
	 */
	public function current(): mixed
	{
		/** @var TValue|false */
		return current($this->items);
	}

	/**
	 * @return TValue|false
	 */
	public function next(): mixed
	{
		/** @var TValue|false */
		return next($this->items);
	}

	/**
	 * @return TValue|false
	 */
	public function prev(): mixed
	{
		/** @var TValue|false */
		return prev($this->items);
	}

	/**
	 * @return TKey|null
	 */
	public function key(): int|string|null
	{
		/** @var TKey|null */
		return key($this->items);
	}

	/**
	 * @return TValue|false
	 */
	public function reset(): mixed
	{
		/** @var TValue|false */
		return reset($this->items);
	}

	public function count(): int
	{
		return count($this->items);
	}
}
