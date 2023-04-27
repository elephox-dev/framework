<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use ArrayAccess;

/**
 * @template TKey
 * @template TValue
 *
 * @extends ArrayAccess<0|1, TKey|TValue>
 */
interface GenericKeyValuePair extends ArrayAccess
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
