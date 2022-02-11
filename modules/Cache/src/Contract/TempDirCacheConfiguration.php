<?php
declare(strict_types=1);

namespace Elephox\Cache\Contract;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
interface TempDirCacheConfiguration extends CacheConfiguration
{
	/**
	 * @return non-empty-string
	 */
	#[Pure]
	public function getCacheId(): string;

	/**
	 * @return non-empty-string
	 */
	#[Pure]
	public function getTempDir(): string;

	/**
	 * @return positive-int|0
	 */
	#[Pure]
	public function getWriteBackThreshold(): int;
}
