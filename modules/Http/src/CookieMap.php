<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use InvalidArgumentException;

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
			/** @psalm-suppress DocblockTypeContradiction */
			if (!is_string($key) || !is_string($value)) {
				throw new InvalidArgumentException('CookieMap::fromGlobals() expects an array of strings with string keys');
			}

			$map->put($key, new Cookie($key, $value));
		}

		return $map;
	}
}
