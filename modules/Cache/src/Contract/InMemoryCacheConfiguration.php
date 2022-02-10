<?php
declare(strict_types=1);

namespace Elephox\Cache\Contract;

use ArrayAccess;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
interface InMemoryCacheConfiguration extends CacheConfiguration
{
	/**
	 * @return class-string<ArrayAccess>|"array"
	 */
	#[Pure]
	public function getCacheImplementation(): string;
}
