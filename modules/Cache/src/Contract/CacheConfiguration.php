<?php
declare(strict_types=1);

namespace Elephox\Cache\Contract;

use DateInterval;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
interface CacheConfiguration
{
	#[Pure]
	public function getDefaultTTL(): DateInterval|int|null;
}
