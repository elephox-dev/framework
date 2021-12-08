<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayAccess;
use ArrayIterator;
use Elephox\Collection\Contract\GenericList;
use Elephox\Collection\Contract\ReadonlyList;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Stringable;

/**
 * @template T
 *
 * @template-implements GenericList<T>
 * @template-implements ArrayAccess<int, T>
 */
class ArrayList implements GenericList, ArrayAccess
{
	/**
	 * @template U
	 *
	 * @param iterable<U> $array
	 * @return self<U>
	 */
	public static function fromArray(iterable $array): self
	{
		if ($array instanceof self)
		{
			return $array;
		}

		return new self($array);
	}

	/**
	 * @template U
	 *
	 * @param U $value
	 * @return self<U>
	 */
	public static function fromValue(mixed $value): self
	{
		return new self([$value]);
	}

	/** @var list<T> */
	private array $list = [];

	/**
	 * @param iterable<T> $items
	 */
	public function __construct(iterable $items = [])
	{
		foreach ($items as $item) {
			$this->add($item);
		}
	}

	#[Pure] public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->list);
	}

	/**
	 * @return T
	 */
	public function offsetGet(mixed $offset): mixed
	{
		if (!is_int($offset)) {
			throw new OffsetNotAllowedException($offset);
		}

		return $this->get($offset);
	}

	/**
	 * @param T $value
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		if ($offset === null) {
			$this->add($value);

			return;
		}

		if (!is_int($offset)) {
			throw new OffsetNotAllowedException($offset);
		}

		$this->set($offset, $value);
	}

	public function offsetUnset($offset): void
	{
		unset($this->list[$offset]);
	}

	#[Pure] public function count(): int
	{
		return count($this->list);
	}

	public function set(int $index, mixed $value): void
	{
		if (!isset($this->list[$index])) {
			throw new InvalidArgumentException("Index is outside of list.");
		}

		$this->list[$index] = $value;
	}

	/**
	 * @return T
	 */
	public function get(int $index): mixed
	{
		if (!$this->offsetExists($index)) {
			throw new OffsetNotFoundException($index);
		}

		return $this->list[$index];
	}

	/**
	 * @param T $value
	 */
	public function add(mixed $value): void
	{
		$this->list[] = $value;
	}

	/**
	 * @param iterable<T> $values
	 */
	public function addAll(iterable $values): void
	{
		foreach ($values as $value) {
			$this->add($value);
		}
	}

	#[Pure] public function first(?callable $filter = null): mixed
	{
		foreach ($this->list as $item) {
			if ($filter === null || $filter($item)) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * @param callable(T): bool $filter
	 * @return ArrayList<T>
	 */
	public function where(callable $filter): ArrayList
	{
		$result = new ArrayList();

		foreach ($this->list as $item) {
			if ($filter($item)) {
				$result->add($item);
			}
		}

		return $result;
	}

	#[Pure] public function isEmpty(): bool
	{
		return empty($this->list);
	}

	/**
	 * @return list<T>
	 */
	#[Pure] public function asArray(): array
	{
		return $this->list;
	}

	/**
	 * @template TOut
	 *
	 * @param callable(T): TOut $callback
	 * @return ArrayList<TOut>
	 */
	public function map(callable $callback): ArrayList
	{
		$arr = new ArrayList();

		foreach ($this->list as $value) {
			/**
			 * @psalm-suppress InvalidArgument Until vimeo/psalm#6821 is fixed
			 */
			$arr->add($callback($value));
		}

		return $arr;
	}

	#[Pure] public function any(?callable $filter = null): bool
	{
		return !$this->isEmpty() && $this->first($filter) !== null;
	}

	/**
	 * @return ArrayIterator<int, T>
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->list);
	}

	/**
	 * @param T $value
	 */
	public function push(mixed $value): void
	{
		$this->add($value);
	}

	/**
	 * @return T
	 */
	public function pop(): mixed
	{
		return array_pop($this->list);
	}

	/**
	 * @return T
	 */
	public function peek(): mixed
	{
		$idx = $this->count() - 1;
		if ($idx < 0) {
			throw new OffsetNotFoundException(0);
		}

		return $this->list[$idx];
	}

	/**
	 * @return T
	 */
	public function shift(): mixed
	{
		$value = array_shift($this->list);

		if ($value === null) {
			throw new OffsetNotFoundException(0);
		}

		return $value;
	}

	/**
	 * @param T $value
	 */
	public function unshift(mixed $value): void
	{
		array_unshift($this->list, $value);
	}

	#[Pure] public function contains(mixed $value): bool
	{
		return $this->any(static fn ($item) => $item === $value);
	}

	/**
	 * @param callable(T, T): int $callback
	 *
	 * @return ArrayList<T>
	 */
	public function orderBy(callable $callback): ArrayList
	{
		usort($this->list, $callback);

		return $this;
	}

	public function join(Stringable|string $separator): string
	{
		return implode((string)$separator, $this->map(fn($item) => (string)$item)->asArray());
	}

	public function asReadonly(): ReadonlyList
	{
		return $this;
	}

	public function __clone()
	{
		$this->list = clone $this->list;
	}
}
