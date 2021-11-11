<?php
declare(strict_types=1);

namespace Elephox\Collection;

/**
 * @template TKey
 * @template TValue
 *
 * @template-implements \Elephox\Collection\Contract\KeyValuePair<TKey, TValue>
 */
class KeyValuePair implements Contract\KeyValuePair
{
	/** @var TKey $key */
	private mixed $key;

	/** @var TValue $value */
	private mixed $value;

	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	public function __construct(mixed $key, mixed $value)
	{
		$this->key = $key;
		$this->value = $value;
	}

	public function getKey(): mixed
	{
		return $this->key;
	}

	public function getValue(): mixed
	{
		return $this->value;
	}
}
