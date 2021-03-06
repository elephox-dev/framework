<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Closure;
use Elephox\Collection\Iterator\EagerCachingIterator;
use Generator;
use Iterator;
use IteratorAggregate;

/**
 * @template-covariant TIteratorKey
 * @template-covariant TSource
 *
 * @implements IteratorAggregate<TIteratorKey, TSource>
 */
class IteratorProvider implements IteratorAggregate
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
	 * @psalm-suppress ImplementedReturnTypeMismatch Psalm seems to have problems with analyzing traits and abstract classes together...
	 *
	 * @return Iterator<TIteratorKey, TSource>
	 */
	public function getIterator(): Iterator
	{
		if ($this->iterator !== null) {
			return $this->iterator;
		}

		assert($this->iteratorGenerator !== null, 'Either iterator or iteratorGenerator must be set');

		$result = ($this->iteratorGenerator)();

		assert($result instanceof Iterator, sprintf('Given iterator generator does not return an iterator, got %s instead', get_debug_type($result)));

		if ($result instanceof Generator) {
			return new EagerCachingIterator($result);
		}

		$this->iterator = $result;

		return $result;
	}
}
