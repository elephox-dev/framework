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
	 * @return Contract\CookieMap
	 */
	public static function fromGlobals(?array $cookie = null): Contract\CookieMap
	{
		$cookie = $cookie ?? $_COOKIE;

		$map = new self();

		/**
		 * @var string $key
		 * @var string|null $value
		 */
		foreach ($cookie as $key => $value) {
			$map->put($key, new Cookie($key, $value));
		}

		return $map;
	}
}
