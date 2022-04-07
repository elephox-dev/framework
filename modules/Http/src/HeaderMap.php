<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\OOR\Casing;

/**
 * @extends ArrayMap<string, string|list<string>>
 */
class HeaderMap extends ArrayMap implements Contract\HeaderMap
{
	/**
	 * @param array<string, string|list<string>>|null $server
	 */
	public static function fromGlobals(?array $server = null): Contract\HeaderMap
	{
		$server ??= $_SERVER;

		$map = new self();

		/**
		 * @var string $name
		 * @var string|list<string> $value
		 */
		foreach ($server as $name => $value) {
			if (!str_starts_with($name, 'HTTP_')) {
				continue;
			}

			$name = Casing::toHttpHeader(substr($name, 5));

			$map->put($name, $value);
		}

		return $map;
	}
}
