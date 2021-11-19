<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Support\Contract\ArrayConvertible;
use Elephox\Collection\Contract\ReadonlyMap;

/**
 * @extends ReadonlyMap<HeaderName, array<int, string>|string>
 * @extends ArrayConvertible<string, array<int, string>|string>
 */
interface ReadonlyHeaderMap extends ReadonlyMap, ArrayConvertible
{
}
