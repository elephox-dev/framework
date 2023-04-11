<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use ArrayAccess;
use Countable;
use LogicException;
use SeekableIterator;
use Traversable;

/**
 * @template TKey
 * @template TValue
 *
 * @template-implements SeekableIterator<TKey, TValue>
 * @template-implements ArrayAccess<array-key, TValue>
 */
class EagerCachingIterator implements SeekableIterator, ArrayAccess, Countable
{
	private array $values = [];
	private array $keys = [];
	private int $pos = 0;
	private bool $iterated = false;

	/**
	 * @param Traversable<TKey, TValue> $iterator
	 */
	public function __construct(private readonly Traversable $iterator)
	{
	}

	public function current(): mixed
	{
		if (!$this->iterated) {
			$this->rewind();
		}

		return $this->values[$this->pos] ?? null;
	}

	public function next(): void
	{
		if (!$this->iterated) {
			$this->rewind();
		}

		$this->pos++;
	}

	public function hasNext(): bool
	{
		if (!$this->iterated) {
			$this->rewind();
		}

		return $this->pos < count($this->values) - 1;
	}

	public function key(): mixed
	{
		if (!$this->iterated) {
			$this->rewind();
		}

		return $this->keys[$this->pos] ?? null;
	}

	public function valid(): bool
	{
		if (!$this->iterated) {
			$this->rewind();
		}

		return isset($this->values[$this->pos]);
	}

	public function seek(int $offset): void
	{
		if (!$this->iterated) {
			$this->rewind();
		}

		$this->pos = $offset;
	}

	public function rewind(): void
	{
		$this->pos = 0;

		if (!$this->iterated) {
			foreach ($this->iterator as $key => $value) {
				$this->values[] = $value;
				$this->keys[] = $key;
			}

			$this->iterated = true;
		}
	}

	public function offsetExists(mixed $offset): bool
	{
		if (!$this->iterated) {
			$this->rewind();
		}

		return isset($this->values[$offset]);
	}

	public function offsetGet(mixed $offset): mixed
	{
		if (!$this->iterated) {
			$this->rewind();
		}

		/** @var TValue */
		return $this->values[$offset];
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new LogicException('Cannot set values in EagerCachingIterator');
	}

	public function offsetUnset(mixed $offset): void
	{
		throw new LogicException('Cannot unset values in EagerCachingIterator');
	}

	public function count(): int
	{
		if (!$this->iterated) {
			$this->rewind();
		}

		return count($this->values);
	}

	public function getCacheValues(): array
	{
		return $this->values;
	}

	public function getCacheKeys(): array
	{
		return $this->keys;
	}
}
