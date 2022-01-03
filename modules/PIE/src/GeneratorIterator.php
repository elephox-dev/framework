<?php
declare(strict_types=1);

namespace Elephox\PIE;

use Generator;
use Iterator;

/**
 * @template TIteratorKey
 * @template TSource
 *
 * @implements Iterator<TIteratorKey, TSource>
 */
class GeneratorIterator implements Iterator
{
	/**
	 * @param Generator<TIteratorKey, TSource> $generator
	 */
	public function __construct(private Generator $generator)
	{
	}

	public function getReturn(): mixed
	{
		return $this->generator->getReturn();
	}

	public function current(): mixed
	{
		return $this->generator->current();
	}

	public function next(): void
	{
		$this->generator->next();
	}

	public function key(): mixed
	{
		return $this->generator->key();
	}

	public function valid(): bool
	{
		return $this->generator->valid();
	}

	public function rewind(): void
	{
		$this->generator->rewind();
	}
}
