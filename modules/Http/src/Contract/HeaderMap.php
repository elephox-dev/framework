<?php
declare(strict_types=1);

namespace Philly\Http\Contract;

use Philly\Collection\Contract\GenericMap;

/**
 * @extends \Philly\Collection\Contract\GenericMap<\Philly\Http\HeaderName, array<int, string>>
 */
interface HeaderMap extends GenericMap, ReadonlyHeaderMap
{
}
