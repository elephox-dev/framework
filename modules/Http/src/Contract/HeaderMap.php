<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericMap;

/**
 * @extends GenericMap<string, array<int, string>>
 */
interface HeaderMap extends GenericMap, ReadonlyHeaderMap
{
}
