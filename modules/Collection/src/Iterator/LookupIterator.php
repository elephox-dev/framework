<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use ArrayIterator;
use Closure;
use Elephox\Collection\DefaultEqualityComparer;
use Iterator;

/**
 * @template TKey
 * @template TGroupKey
 * @template TValue
 *
 * @implements Iterator<TKey, Iterator<TGroupKey, TValue>>
 */
class LookupIterator implements Iterator
{
	private readonly Closure $comparer;

	/**
	 * @var list<TGroupKey>
	 */
	private array $groupKeys = [];

	/**
	 * @var list<list<TValue>>
	 */
	private array $values = [];

	/**
	 * @var list<list<TKey>>
	 */
	private array $keys = [];

	/**
	 * @param Iterator<TKey, TValue> $iterator
	 * @param Closure(TValue, TKey): TGroupKey $groupingFunction
	 * @param Closure(TGroupKey, TGroupKey): bool $comparer
	 */
	public function __construct(
		private readonly Iterator $iterator,
		private readonly Closure $groupingFunction,
		?Closure $comparer = null,
	)
	{
		$this->comparer = $comparer ?? DefaultEqualityComparer::same(...);
	}

	public function current(): mixed
	{
		$idx = key($this->groupKeys);
		if ($idx === null) {
			return null;
		}

		return new GroupingIterator(new ArrayIterator($this->values[$idx]), $this->groupKeys[$idx]);
	}

	public function next(): void
	{
		next($this->groupKeys);
	}

	public function key(): mixed
	{
		return current($this->groupKeys);
	}

	public function valid(): bool
	{
		return key($this->groupKeys) !== null;
	}

	public function rewind(): void
	{
		$this->groupKeys = [];

		/**
		 * @param list<mixed> $xs
		 * @param callable(mixed): bool $f
		 * @return int|null
		 */
		$findIdx = static function(array $xs, callable $f): ?int
		{
			/**
			 * @var int $k
			 * @var mixed $x
			 */
			foreach ($xs as $k => $x) {
				if ($f($x) === true) {
					return $k;
				}
			}

			return null;
		};

		foreach ($this->iterator as $key => $value) {
			$groupingKey = ($this->groupingFunction)($value, $key);
			$idx = $findIdx($this->groupKeys, static fn (mixed $k): bool => (bool)($this->comparer)($k, $groupingKey));
			if ($idx === null) {
				$this->groupKeys[] = $groupingKey;
				$idx = end($this->groupKeys);
			}

			$this->values[$idx][] = $value;
			$this->keys[$idx][] = $key;
		}
	}
}
