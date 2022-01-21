<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use Closure;
use Iterator;

/**
 * @template TKey
 * @template TValue
 * @template TCompareKey
 *
 * @internal
 *
 * @implements Iterator<TKey, TValue>
 */
class OrderedIterator implements Iterator
{
	/**
	 * @var list<array{value: TValue, key: TKey}>
	 */
	protected array $cache = [];

	/**
	 * @var list<Closure(TCompareKey, TCompareKey): int>
	 */
	protected array $comparators = [];

	/**
	 * @var list<Closure(TValue, TKey): TCompareKey>
	 */
	protected array $keySelectors = [];

	/**
	 * @param Iterator $iterator
	 * @param Closure(TValue, TKey): TCompareKey $keySelector
	 * @param Closure(TCompareKey, TCompareKey): int $comparator
	 */
	public function __construct(
		protected Iterator $iterator,
		Closure $keySelector,
		Closure $comparator,
	) {
		if ($this->iterator instanceof self) {
			$this->keySelectors = $this->iterator->keySelectors;
			$this->comparators = $this->iterator->comparators;
			$this->iterator = $this->iterator->iterator;
		}

		$this->keySelectors[] = $keySelector;
		$this->comparators[] = $comparator;
	}

	public function current(): mixed
	{
		$currentRow = current($this->cache);
		if ($currentRow === false) {
			return false;
		}

		return $currentRow['value'];
	}

	public function next(): void
	{
		next($this->cache);
	}

	public function key(): string|int|null
	{
		return key($this->cache);
	}

	public function valid(): bool
	{
		return key($this->cache) !== null;
	}

	public function rewind(): void
	{
		$this->cache = [];

		/**
		 * @var TKey $elementKey
		 * @var TValue $element
		 */
		foreach ($this->iterator as $elementKey => $element) {
			$this->cache[] = [
				'value' => $element,
				'key' => $elementKey,
			];
		}

		usort($this->cache, function (array $a, array $b): int {
			$result = 0;
			foreach ($this->comparators as $index => $comparator) {
				$keySelector = $this->keySelectors[$index];

				// TODO: cache keys
				$keyA = $keySelector($a['value'], $a['key']);
				$keyB = $keySelector($b['value'], $b['key']);

				$result = $comparator($keyA, $keyB);
				if ($result !== 0) {
					break;
				}
			}

			return $result;
		});
	}
}
