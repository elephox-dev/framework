<?php
declare(strict_types=1);

namespace Elephox\Collection;

/**
 * @template TKey
 * @template TValue
 *
 * @implements Contract\GenericKeyValuePair<TKey, TValue>
 */
class KeyValuePair implements Contract\GenericKeyValuePair
{
	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	public function __construct(
		private readonly mixed $key,
		private readonly mixed $value,
	) {
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getValue()
	{
		return $this->value;
	}
}
