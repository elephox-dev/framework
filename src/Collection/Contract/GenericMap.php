<?php

namespace Philly\Collection\Contract;

/**
 * @template TKey
 * @template TValue
 *
 * @extends \Philly\Collection\Contract\ReadonlyMap<TKey, TValue>
 */
interface GenericMap extends ReadonlyMap
{
	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	public function put(mixed $key, mixed $value): void;
}
