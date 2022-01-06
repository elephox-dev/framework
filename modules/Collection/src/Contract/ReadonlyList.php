<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use Countable;
use IteratorAggregate;
use Elephox\Support\Contract\ArrayConvertible;
use JetBrains\PhpStorm\Pure;
use Stringable;

/**
 * @template T
 *
 * @extends GenericCollection<T>
 * @extends IteratorAggregate<int, T>
 * @extends ArrayConvertible<int, T>
 * @extends iterable<T>
 * @extends list<T>
 */
interface ReadonlyList extends GenericCollection, Filterable, Mappable, Countable, IteratorAggregate, ArrayConvertible
{
	/**
	 * @return T
	 */
	#[Pure] public function get(int $index): mixed;

	/**
	 * @param callable(T): bool $filter
	 * @return GenericList<T>
	 */
	public function where(callable $filter): GenericList;

	/**
	 * @template TOut
	 *
	 * @param callable(T): TOut $callback
	 * @return GenericList<TOut>
	 */
	public function map(callable $callback): GenericList;

	#[Pure] public function isEmpty(): bool;

	public function join(string|Stringable $separator): string;

	/**
	 * @return list<T> Returns this object in its array representation.
	 */
	#[Pure] public function asArray(): array;
}
