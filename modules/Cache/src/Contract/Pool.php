<?php
declare(strict_types=1);

namespace Elephox\Cache\Contract;

use ArrayAccess;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

interface Pool extends ArrayAccess, CacheItemPoolInterface
{
	public function getConfiguration(): CacheConfiguration;

	/**
	 * @return iterable<string, CacheItemInterface>
	 */
	public function getItems(array $keys = []): iterable;
}
