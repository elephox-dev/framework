<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template TKey
 * @template TValue
 */
interface KeyValuePair
{
	/**
	 * @return TKey
	 */
	public function getKey(): mixed;

	/**
	 * @return TValue
	 */
	public function getValue(): mixed;
}
