<?php
declare(strict_types=1);

namespace Elephox\PIE;

use JetBrains\PhpStorm\Pure;

/**
 * @template TValue
 * @template TKey
 *
 * @implements GenericIterator<TValue, TKey>
 */
final class AppendIterator implements GenericIterator
{
	/**
	 * @param GenericIterator<TValue, TKey> $first
	 * @param GenericIterator<TValue, TKey> $second
	 */
	#[Pure] public function __construct(
		private GenericIterator $first,
		private GenericIterator $second
	) {
	}

	public function current(): mixed
	{
		if ($this->first->valid()) {
			return $this->first->current();
		}

		return $this->second->current();
	}

	public function next(): void
	{
		if ($this->first->valid()) {
			$this->first->next();
		}

		$this->second->next();
	}

	public function key(): mixed
	{
		if ($this->first->valid()) {
			return $this->first->key();
		}

		return $this->second->key();
	}

	public function valid(): bool
	{
		return $this->first->valid() || $this->second->valid();
	}

	public function rewind(): void
	{
		$this->second->rewind();
		$this->first->rewind();
	}
}
