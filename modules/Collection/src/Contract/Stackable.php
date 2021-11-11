<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template T
 */
interface Stackable
{
	/**
	 * @param T $value
	 */
	public function push(mixed $value): void;

	/**
	 * @return T
	 */
	public function pop(): mixed;

	/**
	 * @return T
	 */
	public function peek(): mixed;

	/**
	 * @return T
	 */
	public function shift(): mixed;

	/**
	 * @param T $value
	 */
	public function unshift(mixed $value): void;
}
