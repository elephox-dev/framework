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

	public function current(): mixed
	{
		return current($this->items);
	}

	public function next(): mixed
	{
		return next($this->items);
	}

	public function prev(): mixed
	{
		return prev($this->items);
	}

	public function key(): int|string|null
	{
		return key($this->items);
	}

	public function reset(): mixed
	{
		return reset($this->items);
	}

	public function count(): int
	{
		return count($this->items);
	}
}
