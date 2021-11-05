<?php

namespace Philly\Collection\Contract;

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
