<?php
declare(strict_types=1);

namespace Philly\Http\Contract;

use Philly\Support\Contract\ArrayConvertible;
use Philly\Collection\Contract\ReadonlyMap;

/**
 * @extends \Philly\Collection\Contract\ReadonlyMap<\Philly\Http\HeaderName, array<int, string>>
 * @extends \Philly\Support\Contract\ArrayConvertible<string, array<int, string>>
 */
interface ReadonlyHeaderMap extends ReadonlyMap, ArrayConvertible
{
}
