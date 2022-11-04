<?php
declare(strict_types=1);

namespace Elephox\Collection\Iterator;

use ArrayIterator;
use Closure;
use Elephox\Collection\DefaultEqualityComparer;
use Elephox\Collection\Contract\Grouping as GroupingContract;
use Elephox\Collection\Grouping;
use Iterator;
use RuntimeException;
use Traversable;

/**
 * @template TGroupKey
 * @template TKey
 * @template TValue
 *
 * @implements Iterator<TGroupKey, GroupingContract<TGroupKey, TKey, TValue>>
 */
class GroupingIterator implements Iterator
{
	private readonly Closure $comparer;

	/**
	 * @var list<TGroupKey>
	 */
	private array $groupKeys = [];

	/**
	 * @var array<int, list<TValue>>
	 */
	private array $values = [];

	/**
	 * @param Traversable<mixed, TValue> $iterator
	 * @param Closure(TValue): TGroupKey $groupingFunction
	 * @param null|Closure(TGroupKey, TGroupKey): bool $comparer
	 */
	public function __construct(
		private readonly Traversable $iterator,
		private readonly Closure $groupingFunction,
		?Closure $comparer = null,
	) {
		$this->comparer = $comparer ?? DefaultEqualityComparer::same(...);
	}

	public function current(): GroupingContract
	{
		$idx = key($this->groupKeys);
		if ($idx === null) {
			throw new RuntimeException('No current group key');
		}

		return new Grouping($this->groupKeys[$idx], new ArrayIterator($this->values[$idx]));
	}

	public function next(): void
	{
		next($this->groupKeys);
	}

	public function key(): mixed
	{
		$idx = key($this->groupKeys);
		if ($idx === null) {
			throw new RuntimeException('No current group key');
		}

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
		 *
		 * @return int|null
		 */
		$findIdx = static function (array $xs, callable $f): ?int {
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

		foreach ($this->iterator as $value) {
			$groupingKey = ($this->groupingFunction)($value);
			$idx = $findIdx($this->groupKeys, fn (mixed $k): bool => (bool) ($this->comparer)($k, $groupingKey));
			if ($idx === null) {
				$this->groupKeys[] = $groupingKey;
				end($this->groupKeys);
				$idx = key($this->groupKeys) ?? throw new RuntimeException('Unexpected null key');
			}

			$this->values[$idx][] = $value;
		}

		reset($this->groupKeys);
	}
}
