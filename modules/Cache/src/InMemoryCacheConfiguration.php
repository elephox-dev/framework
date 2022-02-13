<?php
declare(strict_types=1);

namespace Elephox\Cache;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
class InMemoryCacheConfiguration extends AbstractCacheConfiguration implements Contract\InMemoryCacheConfiguration
{
}
