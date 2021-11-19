<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Support\Contract\ArrayConvertible;
use Elephox\Collection\Contract\ReadonlyMap;

/**
 * @extends ReadonlyMap<non-empty-string, array<int, string>|string>
 * @extends ArrayConvertible<non-empty-string, array<int, string>|string>
 */
interface ReadonlyHeaderMap extends ReadonlyMap, ArrayConvertible
{
}
