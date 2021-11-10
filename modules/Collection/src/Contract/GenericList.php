<?php
declare(strict_types=1);

namespace Philly\Collection\Contract;

/**
 * @template T
 *
 * @extends ReadonlyList<T>
 */
interface GenericList extends ReadonlyList, Stackable
{
	/**
	 * @param T $value
	 */
	public function set(int $index, mixed $value): void;

	/**
	 * @param T $value
	 */
	public function add(mixed $value): void;
}
