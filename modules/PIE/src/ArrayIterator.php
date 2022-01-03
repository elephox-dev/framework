<?php
declare(strict_types=1);

namespace Elephox\PIE;

use ArrayAccess;
use BadMethodCallException;
use JetBrains\PhpStorm\Pure;
use SeekableIterator;

/**
 * @template TKey as array-key
 * @template TValue
 *
 * @implements SeekableIterator<TKey, TValue>
 * @implements ArrayAccess<TKey, TValue>
 */
class ArrayIterator implements SeekableIterator, ArrayAccess
{
	private int $keyIndex = 0;

	/**
	 * @var list<TValue>
	 */
	private array $values;

	/**
	 * @var list<TKey>
	 */
	private array $keys;

	/**
	 * @param array<TKey, TValue> $array
	 */
	public function __construct(array $array)
	{
		$this->values = array_values($array);
		$this->keys = array_keys($array);
	}

	#[Pure] public function current(): mixed
	{
		return $this->values[$this->keyIndex];
	}

	public function next(): void
	{
		$this->keyIndex++;
	}

	#[Pure] public function key(): mixed
	{
		return $this->keys[$this->keyIndex];
	}

	#[Pure] public function valid(): bool
	{
		return $this->keyIndex < count($this->keys);
	}

	public function rewind(): void
	{
		$this->keyIndex = 0;
	}

	public function seek(int $offset): void
	{
		$this->keyIndex = $offset;
	}

	public function offsetExists(mixed $offset): bool
	{
		return array_key_exists($offset, $this->keys);
	}

	public function offsetGet(mixed $offset): mixed
	{
		$index = array_search($offset, $this->keys, true);
		if ($index === false)
		{
			throw new BadMethodCallException("Offset $offset does not exist");
		}

		return $this->values[$index];
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new BadMethodCallException('Cannot set values in an array iterator');
	}

	public function offsetUnset(mixed $offset): void
	{
		throw new BadMethodCallException('Cannot unset values in an array iterator');
	}
}
