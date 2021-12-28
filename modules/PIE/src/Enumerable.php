<?php
declare(strict_types=1);

namespace Elephox\PIE;

use Closure;
use Generator;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

/**
 * @template TSource
 * @template TIteratorKey
 *
 * @implements GenericEnumerable<TSource, TIteratorKey>
 */
class Enumerable implements GenericEnumerable
{
	/**
	 * @uses IsEnumerable<TSource, TIteratorKey>
	 */
	use IsEnumerable;

	/**
	 * @var \Elephox\PIE\GenericIterator<TSource, TIteratorKey>
	 */
	private GenericIterator $iterator;

	/**
	 * @param Closure(): GenericIterator<TSource, TIteratorKey>|GenericIterator<TSource, TIteratorKey>|Generator<TSource, TIteratorKey> $iterator
	 */
	#[Pure] public function __construct(
		GenericIterator|Generator|Closure $iterator
	) {
		if ($iterator instanceof GenericIterator) {
			$this->iterator = $iterator;
		} else if ($iterator instanceof Generator) {
			$this->iterator = new GeneratorIterator($iterator);
		} else {
			$result = $iterator();
			if ($result instanceof GenericIterator) {
				$this->iterator = $result;
			} else if ($result instanceof Generator) {
				$this->iterator = new GeneratorIterator($result);
			} else {
				throw new InvalidArgumentException('The iterator must be or return an instance of GenericIterator');
			}
		}
	}

	/**
	 * @return GenericIterator<TSource, TIteratorKey>
	 */
	#[Pure] public function getIterator(): GenericIterator
	{
		return $this->iterator;
	}
}
