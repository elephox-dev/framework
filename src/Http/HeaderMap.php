<?php

namespace Philly\Base\Http;

use Philly\Base\Collection\Map;
use Philly\Base\Http\Contract\ReadonlyHeaderMap;

/**
 * @extends \Philly\Base\Collection\Map<\Philly\Base\Http\HeaderName, array<int, string>|string>
 */
class HeaderMap extends Map implements ReadonlyHeaderMap
{
}
