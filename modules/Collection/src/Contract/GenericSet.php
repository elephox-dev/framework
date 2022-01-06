<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template T
 *
 * @extends ReadonlySet<T>
 */
interface GenericSet extends ReadonlySet
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
