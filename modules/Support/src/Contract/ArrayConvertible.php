<?php
declare(strict_types=1);

namespace Philly\Support\Contract;

/**
 * Declares the implementation can be represented as an array.
 *
 * @template TKey
 * @template TValue
 */
interface ArrayConvertible
{
	/**
	 * @return array<TKey, TValue> Returns this object in its array representation.
	 */
	public function asArray(): array;
}
