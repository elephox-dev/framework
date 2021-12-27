<?php
declare(strict_types=1);

namespace Elephox\PIE;

use Closure;

/**
 * @template TValue
 * @template TKey
 *
 * @implements GenericIterator<TValue, TKey>
 */
final class LazyIterator implements GenericIterator
{
	/**
	 * @var GenericIterator<TValue, TKey>|null $enumerator
	 */
	private ?GenericIterator $enumerator = null;

	/**
	 * @param Closure(): GenericIterator<TValue, TKey> $enumeratorGenerator
	 */
	public function __construct(
		private Closure $enumeratorGenerator,
	) {
	}

	/**
	 * @return GenericIterator<TValue, TKey>
	 */
	private function getEnumerator(): GenericIterator
	{
		if ($this->enumerator === null)
		{
			$this->enumerator = ($this->enumeratorGenerator)();
		}

		return $this->enumerator;
	}

	public function current(): mixed
	{
		return $this->getEnumerator()->current();
	}

	public function next(): void
	{
		$this->getEnumerator()->next();
	}

	public function key(): mixed
	{
		return $this->getEnumerator()->key();
	}

	public function valid(): bool
	{
		return $this->getEnumerator()->valid();
	}

	public function rewind(): void
	{
		$this->getEnumerator()->rewind();
	}
}
