<?php
declare(strict_types=1);

namespace Elephox\Cache\Contract;

use ArrayAccess;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @extends ArrayAccess<string, CacheItemInterface>
 */
interface Cache extends ArrayAccess, CacheItemPoolInterface
{
	public function getConfiguration(): CacheConfiguration;

	/**
	 * @param array $keys
	 *
	 * @return iterable<string, CacheItemInterface>
	 */
	public function getItems(array $keys = []): iterable;
}
