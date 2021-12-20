<?php
declare(strict_types=1);

namespace Elephox\Collection;

use JetBrains\PhpStorm\Pure;

/**
 * @template TKey
 * @template TValue
 *
 * @template-implements Contract\KeyValuePair<TKey, TValue>
 */
class KeyValuePair implements Contract\KeyValuePair
{
	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	#[Pure] public function __construct(
		private mixed $key,
		private mixed $value,
	) {
	}

	#[Pure] public function getKey(): mixed
	{
		return $this->key;
	}

	#[Pure] public function getValue(): mixed
	{
		return $this->value;
	}
}
