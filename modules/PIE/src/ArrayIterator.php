<?php
declare(strict_types=1);

namespace Elephox\PIE;

use JetBrains\PhpStorm\Pure;

/**
 * @template TValue
 * @template TKey as array-key
 *
 * @implements GenericIterator<TValue, TKey>
 */
class ArrayIterator implements GenericIterator
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
}
