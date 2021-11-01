<?php

namespace Philly\Base\Http\Contract;

use Philly\Base\Collection\Contract\ReadonlyMap;

/**
 * @extends \Philly\Base\Collection\Contract\ReadonlyMap<\Philly\Base\Http\HeaderName, array<int, string>|string>
 */
interface ReadonlyHeaderMap extends ReadonlyMap
{
}
