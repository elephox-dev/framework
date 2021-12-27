<?php
declare(strict_types=1);

namespace Elephox\PIE;

use JetBrains\PhpStorm\Pure;

/**
 * @template TValue
 *
 * @implements GenericIterator<TValue, int>
 */
final class SingleElementIterator implements GenericIterator
{
	private bool $rewound = true;

	/**
	 * @param TValue $element
	 */
	#[Pure] public function __construct(
		private mixed $element
	) {
	}

	#[Pure] public function current(): mixed
	{
		return $this->element;
	}

	/**
	 * @psalm-external-mutation-free
	 */
	public function next(): void
	{
		$this->rewound = false;
	}

	#[Pure] public function key(): int
	{
		return 0;
	}

	#[Pure] public function valid(): bool
	{
		return $this->rewound;
	}

	/**
	 * @psalm-external-mutation-free
	 */
	public function rewind(): void
	{
		$this->rewound = true;
	}
}
