<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Support\Contract\ArrayConvertible;
use Elephox\Collection\Contract\ReadonlyMap;

/**
 * @extends ReadonlyMap<string, array<int, string>>
 * @extends ArrayConvertible<string, array<int, string>>
 */
interface ReadonlyHeaderMap extends ReadonlyMap, ArrayConvertible
{
}
