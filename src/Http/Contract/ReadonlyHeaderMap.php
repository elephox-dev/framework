<?php

namespace Philly\Http\Contract;

use Philly\Collection\Contract\ReadonlyMap;

/**
 * @extends \Philly\Collection\Contract\ReadonlyMap<\Philly\Http\HeaderName, array<int, string>|string>
 */
interface ReadonlyHeaderMap extends ReadonlyMap
{
}
