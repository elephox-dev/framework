<?php
declare(strict_types=1);

namespace Elephox\Collection;

use BadMethodCallException;

/**
 * @template TKey
 * @template TValue
 *
 * @implements Contract\GenericKeyValuePair<TKey, TValue>
 */
readonly class KeyValuePair implements Contract\GenericKeyValuePair
{
	/**
	 * @param TKey $key
	 * @param TValue $value
	 */
	public function __construct(
		private mixed $key,
		private mixed $value,
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

	public function offsetExists(mixed $offset): bool
	{
		return $offset === 0 || $offset === 1;
	}

	public function offsetGet(mixed $offset): mixed
	{
		if ($offset === 0) {
			return $this->key;
		}

		if ($offset === 1) {
			return $this->value;
		}

		throw new OffsetNotAllowedException($offset);
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new BadMethodCallException("KeyValuePair doesn't allow setting of values");
	}

	public function offsetUnset(mixed $offset): void
	{
		throw new BadMethodCallException("KeyValuePair doesn't allow deleting of values");
	}
}
