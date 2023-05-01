<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template T
 *
 * @extends GenericList<T>
 */
interface GenericArrayList extends GenericList
{
	/**
	 * @return T|false
	 */
	public function current(): mixed;

	/**
	 * @return T|false
	 */
	public function next(): mixed;

	/**
	 * @return T|false
	 */
	public function prev(): mixed;

	public function key(): ?int;

	/**
	 * @return T|false
	 */
	public function reset(): mixed;
}
