<?php

namespace Philly\Http;

use Philly\Collection\HashMap;

/**
 * @extends \Philly\Collection\HashMap<\Philly\Http\HeaderName, array<int, string>>
 */
class HeaderMap extends HashMap implements Contract\HeaderMap
{
}
