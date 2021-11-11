<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

/**
 * @template TKey
 * @template TValue
 *
 * @extends \Elephox\Collection\Contract\ReadonlyMap<TKey, TValue>
 */
interface GenericMap extends ReadonlyMap
{
	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	public function put(mixed $key, mixed $value): void;
}
