<?php
declare(strict_types=1);

namespace Elephox\PIE;

use Closure;
use Generator;
use InvalidArgumentException;
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
	 * @var \Elephox\PIE\GenericIterator<T, TKey>
	 */
	private GenericIterator $iterator;

	/**
	 * @param Closure(): GenericIterator<T, TKey>|GenericIterator<T, TKey>|Generator<T, TKey> $iterator
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
				throw new InvalidArgumentException(
					'The iterator must be or return an instance of GenericIterator'
				);
			}
		}
	}

	/**
	 * @return GenericIterator<T, TKey>
	 */
	#[Pure] public function getIterator(): GenericIterator
	{
		return $this->iterator;
	}
}
