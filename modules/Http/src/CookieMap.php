<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Http\Contract\Cookie;

/**
 * @extends ArrayMap<string, Cookie>
 */
class CookieMap extends ArrayMap implements Contract\CookieMap
{
}
