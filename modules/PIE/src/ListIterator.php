<?php
declare(strict_types=1);

namespace Elephox\PIE;

use JetBrains\PhpStorm\Pure;

/**
 * @template TValue
 *
 * @implements GenericIterator<TValue, int>
 */
class ListIterator implements GenericIterator
{
	private int $index = 0;

	/**
	 * @param list<TValue> $list
	 */
	public function __construct(
		private array $list
	) {
		if (!array_is_list($list)) {
			throw new \InvalidArgumentException('ListIterator expects a list');
		}
	}

	#[Pure] public function current(): mixed
	{
		return $this->list[$this->index];
	}

	/**
	 * @psalm-external-mutation-free
	 */
	public function next(): void
	{
		$this->index++;
	}

	#[Pure] public function key(): int
	{
		return $this->index;
	}

	#[Pure] public function valid(): bool
	{
		return $this->index < count($this->list);
	}

	/**
	 * @psalm-external-mutation-free
	 */
	public function rewind(): void
	{
		$this->index = 0;
	}
}
