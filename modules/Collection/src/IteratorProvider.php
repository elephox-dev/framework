<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Closure;
use Elephox\Collection\Iterator\EagerCachingIterator;
use Generator;
use Iterator;
use IteratorAggregate;
use Traversable;

/**
 * @template-covariant TIteratorKey
 * @template-covariant TSource
 *
 * @implements IteratorAggregate<TIteratorKey, TSource>
 */
class IteratorProvider implements IteratorAggregate
{
	/**
	 * @var null|Traversable<TIteratorKey, TSource>
	 */
	private ?Traversable $iterator;

	/**
	 * @var null|Closure(): Traversable<TIteratorKey, TSource>
	 */
	private readonly ?Closure $iteratorGenerator;

	/**
	 * @param Traversable<TIteratorKey, TSource>|Closure(): Traversable<TIteratorKey, TSource> $iterator
	 */
	public function __construct(
		Traversable|Closure $iterator,
	) {
		if ($iterator instanceof Generator) {
			$this->iterator = new EagerCachingIterator($iterator);
			$this->iteratorGenerator = null;
		} elseif ($iterator instanceof Iterator) {
			$this->iterator = $iterator;
			$this->iteratorGenerator = null;
		} else {
			/** @var Closure(): Traversable<TIteratorKey, TSource> $iterator */
			assert(is_callable($iterator));

			$this->iterator = null;
			$this->iteratorGenerator = $iterator;
		}
	}

	/**
	 * @psalm-suppress ImplementedReturnTypeMismatch Psalm seems to have problems with analyzing traits and abstract classes together...
	 *
	 * @return Traversable<TIteratorKey, TSource>
	 */
	public function getIterator(): Traversable
	{
		if ($this->iterator !== null) {
			return $this->iterator;
		}

		assert($this->iteratorGenerator !== null, 'Either iterator or iteratorGenerator must be set');

		$result = ($this->iteratorGenerator)();

		assert($result instanceof Traversable, sprintf('Given iterator generator does not return a Traversable, got %s instead', get_debug_type($result)));

		if ($result instanceof Generator) {
			return new EagerCachingIterator($result);
		}

		$this->iterator = $result;

		return $result;
	}
}
