<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Contract\GenericArrayMap;
use InvalidArgumentException;
use Iterator;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements GenericArrayMap<TKey, TValue>
 */
class ArrayMap implements GenericArrayMap
{
	// TODO: replace generic enumerable function with array-specific functions where possible
	/**
	 * @use IsKeyedEnumerable<TKey, TValue>
	 */
	use IsKeyedEnumerable {
		//		IsKeyedEnumerable::contains as genericContains;
		IsKeyedEnumerable::firstOrDefault as genericFirstOrDefault;
		IsKeyedEnumerable::count as genericCount;
	}

	/**
	 * @use IsArrayEnumerable<TKey, TValue>
	 */
	use IsArrayEnumerable {
		IsArrayEnumerable::contains as arrayContains;
		IsArrayEnumerable::containsKey as arrayContainsKey;
		IsArrayEnumerable::count as arrayCount;
		//		IsArrayEnumerable::firstOrDefault as arrayFirstOrDefault;
	}

	/**
	 * @template UKey of array-key
	 * @template UValue
	 *
	 * @param ArrayMap<UKey, UValue>|iterable<UKey, UValue> $value
	 *
	 * @return self<UKey, UValue>
	 */
	public static function from(mixed $value): self
	{
		if ($value instanceof self) {
			return $value;
		}

		if (is_array($value)) {
			return new self($value);
		}

		if ($value instanceof Iterator) {
			return new self(iterator_to_array($value));
		}

		throw new InvalidArgumentException('Cannot create ArrayMap from given value');
	}

	/**
	 * @param array<TKey, TValue> $items
	 */
	public function __construct(
		protected array $items = [],
	) {
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->items);
	}

	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	public function put(mixed $key, mixed $value): bool
	{
		$validKey = $this->validateKey($key);

		$existed = $this->has($validKey);

		$this->items[$validKey] = $value;

		return !$existed;
	}

	/**
	 * @param TKey $key
	 *
	 * @return TValue
	 *
	 * @throws OffsetNotFoundException
	 */
	public function get(mixed $key): mixed
	{
		$validKey = $this->validateKey($key);

		if (!$this->has($validKey)) {
			throw new OffsetNotFoundException($key);
		}

		return $this->items[$validKey];
	}

	/**
	 * @param TKey $key
	 */
	public function has(mixed $key): bool
	{
		return array_key_exists($key, $this->items);
	}

	/**
	 * @param TKey $key
	 */
	public function remove(mixed $key): bool
	{
		$validKey = $this->validateKey($key);

		if (!$this->has($validKey)) {
			return false;
		}

		if (is_int($validKey)) {
			array_splice($this->items, $validKey, 1);
		} else {
			unset($this->items[$validKey]);
		}

		return true;
	}

	public function clear(): void
	{
		$this->items = [];
	}

	/**
	 * @return array-key
	 */
	protected function validateKey(mixed $key): string|int
	{
		if (!is_string($key) && !is_int($key)) {
			throw new OffsetNotAllowedException($key);
		}

		return $key;
	}

	public function offsetExists(mixed $offset): bool
	{
		return $this->has($offset);
	}

	public function offsetGet(mixed $offset): mixed
	{
		return $this->get($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if ($offset === null) {
			throw new InvalidArgumentException('Cannot set null offset in ArrayMap');
		}

		$this->put($offset, $value);
	}

	public function offsetUnset(mixed $offset): void
	{
		$this->remove($offset);
	}

	public function contains(mixed $value, ?callable $comparer = null): bool
	{
		return $this->arrayContains($value, $comparer);
	}

	/**
	 * @param TKey $key
	 * @param null|callable(TKey, TKey): bool $comparer
	 */
	public function containsKey(mixed $key, ?callable $comparer = null): bool
	{
		return $this->arrayContainsKey($key, $comparer);
	}

	/**
	 * @template TDefault
	 *
	 * @param TDefault $defaultValue
	 * @param null|callable(TValue, TKey): bool $predicate
	 *
	 * @return TDefault|TValue
	 */
	public function firstOrDefault(mixed $defaultValue, ?callable $predicate = null): mixed
	{
		/** @var TValue|TDefault */
		return $this->genericFirstOrDefault($defaultValue, $predicate);
	}

	/**
	 * @param null|callable(TValue, TKey, Iterator<TKey, TValue>): bool $predicate
	 *
	 * @return int<0, max>
	 */
	public function count(?callable $predicate = null): int
	{
		if ($predicate === null) {
			return $this->arrayCount();
		}

		return $this->genericCount($predicate);
	}
}
