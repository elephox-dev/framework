<?php

namespace Philly\Http;

use Philly\Collection\HashMap;
use Philly\Http\Contract\ReadonlyHeaderMap;

/**
 * @extends \Philly\Collection\HashMap<\Philly\Http\HeaderName, array<int, string>|string>
 */
class HeaderMap extends HashMap implements ReadonlyHeaderMap
{
}
