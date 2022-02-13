<?php
declare(strict_types=1);

namespace Elephox\Cache\Contract;

use JetBrains\PhpStorm\Immutable;
use Psr\Cache\CacheItemInterface;

#[Immutable]
interface CacheItem extends CacheItemInterface
{
}
