<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use Elephox\Collection\EmptySequenceException;

/**
 * @template T
 *
 * @extends GenericReadonlyList<T>
 */
interface GenericList extends GenericReadonlyList
{
	/**
	 * @param T $value
	 */
	public function add(mixed $value): bool;

	/**
	 * @param iterable<mixed, T> $values
	 */
	public function addAll(iterable $values): bool;

	/**
	 * @param T $value
	 * @param null|callable(T, T): bool $comparer
	 */
	public function removeValue(mixed $value, ?callable $comparer = null): bool;

	/**
	 * @param T $value
	 * @param int $index
	 */
	public function put(int $index, mixed $value): bool;

	/**
	 * @param int $index
	 * @param T $value
	 */
	public function insertAt(int $index, mixed $value): void;

	/**
	 * @return T
	 *
	 * @param int $index
	 */
	public function removeAt(int $index): mixed;

	public function clear(): void;

	/**
	 * @param null|callable(T, int): bool $predicate
	 *
	 * @return T
	 *
	 * @throws EmptySequenceException
	 */
	public function pop(?callable $predicate = null): mixed;

	/**
	 * @param null|callable(T, int): bool $predicate
	 *
	 * @return T
	 *
	 * @throws EmptySequenceException
	 */
	public function shift(?callable $predicate = null): mixed;

	/**
	 * @param T $value
	 */
	public function unshift(mixed $value): void;
}
