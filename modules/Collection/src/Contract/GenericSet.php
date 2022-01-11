<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use IteratorAggregate;

/**
 * @template T
 *
 * @extends GenericCollection<T>
 * @extends IteratorAggregate<mixed, T>
 */
interface GenericSet extends GenericCollection, IteratorAggregate
{
	/**
	 * @param T $value
	 * @return bool
	 */
	public function add(mixed $value): bool;

	/**
	 * @param T $value
	 * @return bool
	 */
	public function remove(mixed $value): bool;

	/**
	 * @param T $value
	 * @return bool
	 */
	public function contains(mixed $value): bool;
}
