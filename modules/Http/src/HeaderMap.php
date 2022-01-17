<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;

/**
 * @extends ArrayMap<string, string|list<string>>
 */
class HeaderMap extends ArrayMap implements Contract\HeaderMap
{
	public static function fromGlobals(?array $server = null): Contract\HeaderMap
	{
		$server ??= $_SERVER;

		$map = new self;

		foreach ($server as $name => $value) {
			if (!str_starts_with($name, 'HTTP_')) {
				continue;
			}

			$name = str_replace('_', '-', substr($name, 5));
			$map->put($name, $value);
		}

		return $map;
	}
}
