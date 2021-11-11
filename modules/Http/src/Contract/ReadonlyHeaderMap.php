<?php
declare(strict_types=1);

namespace Philly\Http\Contract;

use Philly\Support\Contract\ArrayConvertible;
use Philly\Collection\Contract\ReadonlyMap;

/**
 * @extends ReadonlyMap<string, array<int, string>>
 * @extends ArrayConvertible<string, array<int, string>>
 */
interface ReadonlyHeaderMap extends ReadonlyMap, ArrayConvertible
{
}
