<?php
declare(strict_types=1);

namespace Elephox\Cache;

use DateInterval;
use DateTime;
use Elephox\Cache\Contract\Cache;
use Exception;
use LogicException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;

abstract class AbstractCache implements Cache
{
	/**
	 * @throws \Elephox\Cache\InvalidTtlException
	 *
	 * @param DateTime $offset
	 */
	protected function calculateExpiresAt(DateTime $offset): ?DateTime
	{
		$ttl = $this->getConfiguration()->getDefaultTTL();
		if ($ttl === null) {
			return null;
		}

		if (is_int($ttl)) {
			try {
				$offset->add(new DateInterval('PT' . $ttl . 'S'));
			} catch (Exception $e) {
				throw new InvalidTtlException($ttl, previous: $e);
			}
		} else {
			$offset->add($ttl);
		}

		return $offset;
	}

	/**
	 * @throws InvalidArgumentException
	 *
	 * @param mixed $offset
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
	 *
	 * @param mixed $offset
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
	 *
	 * @param mixed $offset
	 * @param mixed $value
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
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset(mixed $offset): void
	{
		if (!is_string($offset)) {
			throw new InvalidKeyTypeException($offset);
		}

		$this->deleteItem($offset);
	}
}
