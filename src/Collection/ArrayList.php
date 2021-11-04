<?php

namespace Philly\Collection;

use ArrayAccess;
use JetBrains\PhpStorm\Pure;
use Philly\Collection\Contract\GenericList;
use Philly\Support\Contract\ArrayConvertible;

/**
 * @template T
 *
 * @template-implements GenericList<T>
 * @template-implements ArrayAccess<int, T>
 * @template-implements ArrayConvertible<int, T>
 */
class ArrayList implements GenericList, ArrayAccess, ArrayConvertible
{
	public static function fromArray(array $array): self
	{
		return new self($array);
	}

	/** @var array<int, T> */
	private array $list = [];

	/**
	 * @param array<array-key, T> $items
	 */
	public function __construct(array $items = [])
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
		$this->set($this->count(), $value);
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

	#[Pure] public function where(callable $filter): ArrayList
	{
		$result = new ArrayList();

		foreach ($this->list as $item) {
			if ($filter($item)) {
				/** @psalm-suppress ImpureMethodCall Since this call is on another instance of ArrayList. */
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
	 * @return array<int, T>
	 */
	#[Pure] public function asArray(): array
	{
		return $this->list;
	}

	/**
	 * @template TOut
	 *
	 * @param callable(T): TOut $callback
	 * @return \Philly\Collection\ArrayList<TOut>
	 */
	#[Pure] public function map(callable $callback): ArrayList
	{
		$arr = new ArrayList();

		foreach ($this->list as $value) {
			/**
			 * @psalm-suppress InvalidArgument Until vimeo/psalm#6821 is fixed
			 * @psalm-suppress ImpureMethodCall Since this call is on another instance of ArrayList.
			 */
			$arr->add($callback($value));
		}

		return $arr;
	}

	#[Pure] public function any(?callable $filter = null): bool
	{
		return !$this->isEmpty() && $this->first($filter) !== null;
	}
}
