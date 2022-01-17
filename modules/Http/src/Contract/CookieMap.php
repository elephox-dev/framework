<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\GenericMap;

/**
 * @extends GenericMap<string, Cookie>
 */
interface CookieMap extends GenericMap
{
	public static function fromGlobals(?array $cookie = null): CookieMap;
}
