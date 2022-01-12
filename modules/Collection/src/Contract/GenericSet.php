<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template T
 *
 * @extends GenericEnumerable<T>
 */
interface GenericSet extends GenericEnumerable
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
}
