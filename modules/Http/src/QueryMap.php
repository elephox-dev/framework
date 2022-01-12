<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;

/**
 * @extends ArrayMap<string, int|string|list<int|string>>
 */
class QueryMap extends ArrayMap implements Contract\QueryMap
{
}
