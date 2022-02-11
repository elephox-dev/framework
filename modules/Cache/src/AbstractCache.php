<?php
declare(strict_types=1);

namespace Elephox\Cache;

use Elephox\Cache\Contract\Cache;
use LogicException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;

abstract class AbstractCache implements Cache
{
	/**
	 * @throws InvalidArgumentException
	 */
	public function offsetExists(mixed $offset): bool
	{
		if (!is_string($offset)) {
			throw new InvalidKeyTypeException($offset);
		}

		return $this->hasItem($offset);
	}

	/**
	 * @throws InvalidKeyTypeException
	 * @throws \Psr\Cache\InvalidArgumentException
	 */
	public function offsetGet(mixed $offset): CacheItemInterface
	{
		if (!is_string($offset)) {
			throw new InvalidKeyTypeException($offset);
		}

		return $this->getItem($offset);
	}

	/**
	 * @throws InvalidValueTypeException
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		if ($offset !== null) {
			throw new LogicException('Cannot add cache item with a non-null key to an InMemoryCache');
		}

		if (!$value instanceof CacheItemInterface) {
			throw new InvalidValueTypeException($value);
		}

		$this->save($value);
	}

	/**
	 * @throws InvalidKeyTypeException
	 * @throws InvalidArgumentException
	 */
	public function offsetUnset(mixed $offset): void
	{
		if (!is_string($offset)) {
			throw new InvalidKeyTypeException($offset);
		}

		$this->deleteItem($offset);
	}
}
