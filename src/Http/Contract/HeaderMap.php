<?php

namespace Philly\Http\Contract;

use Philly\Collection\Contract\GenericMap;

/**
 * @extends \Philly\Collection\Contract\GenericMap<\Philly\Http\HeaderName, array<int, string>|string>
 */
interface HeaderMap extends GenericMap
{
}
