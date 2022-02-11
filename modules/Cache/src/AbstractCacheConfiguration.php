<?php
declare(strict_types=1);

namespace Elephox\Cache;

use DateInterval;
use Elephox\Cache\Contract\CacheConfiguration;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
abstract class AbstractCacheConfiguration implements CacheConfiguration
{
	public function __construct(
		private readonly DateInterval|int|null $ttl = null,
	)
	{
	}

	#[Pure]
	public function getDefaultTTL(): DateInterval|int|null
	{
		return $this->ttl;
	}
}
