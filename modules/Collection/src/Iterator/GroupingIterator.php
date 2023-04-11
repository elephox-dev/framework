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
	 * @var array<int, list<array{TKey, TValue}>>
	 */
	private array $groupPairs = [];

	/**
	 * @param Traversable<TKey, TValue> $iterator
	 * @param Closure(TValue, TKey): TGroupKey $groupingFunction
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

		$groupKey = $this->groupKeys[$idx];
		$pairs = $this->groupPairs[$idx];

		$iterator = new SelectIterator(
			new KeySelectIterator(
				new ArrayIterator($pairs),
				static fn (int $idx, array $pair): mixed => $pair[0],
			),
			static fn (array $pair, mixed $key): mixed => $pair[1],
		);

		return new Grouping($groupKey, $iterator);
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

		foreach ($this->iterator as $key => $value) {
			$groupingKey = ($this->groupingFunction)($value, $key);
			$idx = null;
			foreach ($this->groupKeys as $i => $k) {
				if (($this->comparer)($k, $groupingKey)) {
					$idx = $i;

					break;
				}
			}

			if ($idx === null) {
				$this->groupKeys[] = $groupingKey;
				end($this->groupKeys);
				$idx = key($this->groupKeys);
			}

			$this->groupPairs[$idx][] = [$key, $value];
		}

		reset($this->groupKeys);
	}
}
