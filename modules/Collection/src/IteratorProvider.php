<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Closure;
use Elephox\Collection\Iterator\EagerCachingIterator;
use Generator;
use InvalidArgumentException;
use Iterator;

/**
 * @template-covariant TIteratorKey
 * @template-covariant TSource
 */
class IteratorProvider
{
	/**
	 * @var null|Iterator<TIteratorKey, TSource>
	 */
	private ?Iterator $iterator;

	/**
	 * @var null|Closure(): Iterator<TIteratorKey, TSource>
	 */
	private readonly ?Closure $iteratorGenerator;

	/**
	 * @param Iterator<TIteratorKey, TSource>|Closure(): Iterator<TIteratorKey, TSource> $iterator
	 */
	public function __construct(
		Iterator|Closure $iterator,
	) {
		if ($iterator instanceof Generator) {
			$this->iterator = new EagerCachingIterator($iterator);
			$this->iteratorGenerator = null;
		} elseif ($iterator instanceof Iterator) {
			$this->iterator = $iterator;
			$this->iteratorGenerator = null;
		} else {
			$this->iterator = null;
			$this->iteratorGenerator = $iterator;
		}
	}

	/**
	 * @return Iterator<TIteratorKey, TSource>
	 */
	public function getIterator(): Iterator
	{
		if ($this->iterator !== null) {
			return $this->iterator;
		}

		assert($this->iteratorGenerator !== null, 'Either iterator or iteratorGenerator must be set');

		$result = ($this->iteratorGenerator)();

		/** @psalm-suppress DocblockTypeContradiction */
		if (!$result instanceof Iterator) {
			throw new InvalidArgumentException('Given iterator generator does not return an iterator');
		}

		if ($result instanceof Generator) {
			return new EagerCachingIterator($result);
		}

		$this->iterator = $result;

		return $result;
	}
}
