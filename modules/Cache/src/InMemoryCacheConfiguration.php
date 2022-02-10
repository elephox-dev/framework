<?php
declare(strict_types=1);

namespace Elephox\Cache;

use DateInterval;
use Elephox\Collection\ArrayMap;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use ArrayAccess;

#[Immutable]
class InMemoryCacheConfiguration  extends DefaultCacheConfiguration implements Contract\InMemoryCacheConfiguration
{
	/**
	 * @param \DateInterval|int|null $ttl
	 * @param class-string<ArrayAccess>|"array" $cacheImplementation
	 */
	#[Pure]
	public function __construct(
		DateInterval|int|null $ttl = null,
		private readonly string $cacheImplementation = ArrayMap::class,
	) {
		parent::__construct($ttl);
	}

	#[Pure]
	public function getCacheImplementation(): string
	{
		return $this->cacheImplementation;
	}
}
