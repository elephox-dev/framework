<?php
declare(strict_types=1);

namespace Philly\Http\Contract;

use Philly\Collection\Contract\GenericMap;

/**
 * @extends GenericMap<string, array<int, string>>
 */
interface HeaderMap extends GenericMap, ReadonlyHeaderMap
{
}
