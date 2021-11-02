<?php

namespace Philly\Collection\Contract;

/**
 * @template T
 *
 * @extends \Philly\Collection\Contract\ReadonlyList<T>
 */
interface GenericList extends ReadonlyList
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
