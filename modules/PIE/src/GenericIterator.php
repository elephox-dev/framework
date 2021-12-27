<?php
declare(strict_types=1);

namespace Elephox\PIE;

use Iterator;

/**
 * @template TValue
 * @template TKey
 *
 * @extends Iterator<TValue>
 */
interface GenericIterator extends Iterator
{
	/**
	 * @return TValue
	 */
	public function current(): mixed;

	public function next(): void;

	/**
	 * @return TKey
	 */
	public function key(): mixed;

	/**
	 * @return bool
	 */
	public function valid(): bool;

	public function rewind(): void;
}
