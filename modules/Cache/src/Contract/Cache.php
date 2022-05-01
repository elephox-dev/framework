<?php
declare(strict_types=1);

namespace Elephox\Cache\Contract;

use ArrayAccess;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

/**
 * @extends ArrayAccess<string, CacheItemInterface>
 */
interface Cache extends ArrayAccess, CacheItemPoolInterface
{
	public function getConfiguration(): CacheConfiguration;

	/**
	 * @param iterable $keys
	 *
	 * @return iterable<string, CacheItemInterface>
	 *
	 * @throws InvalidArgumentException
	 */
	public function getItems(iterable $keys = []): iterable;

	/**
	 * @param iterable $keys
	 *
	 * @return bool
	 *
	 * @throws InvalidArgumentException
	 */
	public function deleteItems(iterable $keys): bool;
}
