<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Closure;
use Elephox\Collection\Contract\Grouping;
use Elephox\Collection\Iterator\FlipIterator;
use Elephox\Collection\Iterator\LookupIterator;
use Elephox\Support\DeepCloneable;
use Iterator;

/**
 * @template TKey
 * @template TElement
 *
 * @implements Contract\Lookup<TKey, TElement>
 */
class Lookup implements Contract\Lookup
{
	/**
	 * @use IsEnumerable<Grouping<TKey, TElement>>
	 */
	use IsEnumerable, DeepCloneable;

	/**
	 * @var Closure(TKey, TKey): bool
	 */
	private readonly Closure $comparer;

	/**
	 * @param null|Closure(TKey, TKey): bool $comparer
	 */
	public function __construct(
		private readonly Iterator $iterator,
		?Closure $comparer = null
	)
	{
		$this->comparer = $comparer ?? DefaultEqualityComparer::same(...);
	}

	public function getIterator(): Iterator
	{
		return new LookupIterator($this->iterator);
	}

	public function get(mixed $key): mixed
	{
		return $this->getGrouping($key);
	}

	/**
	 * @param TKey $key
	 * @return Grouping<TKey, TElement>
	 */
	protected function getGrouping(mixed $key): Grouping
	{
		/**
		 * @var TKey $groupKey
		 */
		foreach ($this->getIterator() as $groupKey => $value) {
			if (($this->comparer)($key, $groupKey)) {
				return $value;
			}
		}

		throw new EmptySequenceException();
	}

	public function containsKey(mixed $key): bool
	{
		/**
		 * @var TKey $groupKey
		 */
		foreach (new FlipIterator($this->getIterator()) as $groupKey) {
			if (($this->comparer)($key, $groupKey)) {
				return true;
			}
		}

		return false;
	}
}
