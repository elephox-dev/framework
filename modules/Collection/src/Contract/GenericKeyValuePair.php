<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template TKey
 * @template TValue
 */
interface GenericKeyValuePair
{
	/**
	 * @return TKey
	 */
	public function getKey();

	/**
	 * @return TValue
	 */
	public function getValue();
}
