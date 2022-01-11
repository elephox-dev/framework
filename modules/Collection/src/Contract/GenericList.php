<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use Countable;
use Elephox\Support\Contract\ArrayConvertible;
use IteratorAggregate;
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
interface GenericList extends GenericCollection, Filterable, Mappable, Countable, IteratorAggregate, ArrayConvertible, Stackable
{
	/**
	 * @param T $value
	 */
	public function set(int $index, mixed $value): void;

	/**
	 * @param T $value
	 */
	public function add(mixed $value): void;

	/**
	 * @param int $index
	 *
	 * @return bool
	 */
	public function removeAt(int $index): bool;

	/**
	 * @param callable(T, int): bool $predicate
	 *
	 * @return bool
	 */
	public function remove(callable $predicate): bool;

	/**
	 * @param callable(T, T): int $callback
	 *
	 * @return GenericList<T>
	 */
	public function orderBy(callable $callback): GenericList;

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

	#[Pure] public function isEmpty(): bool;

	public function join(string|Stringable $separator): string;

	/**
	 * @return list<T> Returns this object in its array representation.
	 */
	public function asArray(): array;
}
