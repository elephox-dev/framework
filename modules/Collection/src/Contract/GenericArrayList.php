<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use ArrayAccess;

/**
 * @template T
 *
 * @extends GenericList<T>
 * @extends ArrayAccess<int, T>
 */
interface GenericArrayList extends GenericList, ArrayAccess
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
