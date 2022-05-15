<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;

/**
 * @extends ArrayMap<string, Contract\Cookie>
 */
class CookieMap extends ArrayMap implements Contract\CookieMap
{
	/**
	 * @param array<string, string|null>|null $cookie
	 */
	public static function fromGlobals(?array $cookie = null): Contract\CookieMap
	{
		$cookie ??= $_COOKIE;

		$map = new self();

		/**
		 * @var string $key
		 * @var string|null $value
		 */
		foreach ($cookie as $key => $value) {
			assert(is_string($key) && ($value === null || is_string($value)), 'CookieMap::fromGlobals() expects an array of strings with string keys');

			$map->put($key, new Cookie($key, $value));
		}

		return $map;
	}
}
