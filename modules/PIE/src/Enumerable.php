<?php
declare(strict_types=1);

namespace Elephox\PIE;

use JetBrains\PhpStorm\Pure;

/**
 * @template T
 * @template TKey
 *
 * @implements GenericEnumerable<T, TKey>
 */
class Enumerable implements GenericEnumerable
{
	/**
	 * @uses IsEnumerable<T, TKey>
	 */
	use IsEnumerable;

	/**
	 * @param GenericIterator<T, TKey> $iterator
	 */
	#[Pure] public function __construct(
		private GenericIterator $iterator
	) {
	}

	/**
	 * @return GenericIterator<T, TKey>
	 */
	#[Pure] public function getIterator(): GenericIterator
	{
		return $this->iterator;
	}
}
