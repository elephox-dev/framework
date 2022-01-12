<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;

/**
 * @extends ArrayMap<string, list<string>>
 */
class HeaderMap extends ArrayMap implements Contract\HeaderMap
{
}
