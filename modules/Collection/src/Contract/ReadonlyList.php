<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use Countable;
use IteratorAggregate;
use Elephox\Support\Contract\ArrayConvertible;
use Stringable;

/**
 * @template T
 *
 * @extends GenericCollection<T>
 * @extends IteratorAggregate<int, T>
 * @extends ArrayConvertible<int, T>
 */
interface ReadonlyList extends GenericCollection, Countable, IteratorAggregate, ArrayConvertible
{
	/**
	 * @return T
	 */
	public function get(int $index): mixed;

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

	public function isEmpty(): bool;

	public function join(string|Stringable $separator): string;

	/**
	 * @return list<T> Returns this object in its array representation.
	 */
	public function asArray(): array;
}
