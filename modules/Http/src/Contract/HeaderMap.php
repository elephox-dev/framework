<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericMap;

/**
 * @extends GenericMap<non-empty-string, array<int, string>|string>
 */
interface HeaderMap extends GenericMap, ReadonlyHeaderMap
{
}
