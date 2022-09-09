<?php
declare(strict_types=1);

namespace Elephox\Web\Routing;

use Elephox\Collection\ArrayMap;

/**
 * @extends ArrayMap<non-empty-string, null|string>
 *
 * @psalm-consistent-constructor
 */
class MatchedUrlParametersMap extends ArrayMap
{
	public static function fromRegex(string $url, string $pattern): static
	{
		preg_match_all($pattern, $url, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);

		$map = new static();

		foreach ($matches[0] as $key => $value) {
			if (!is_string($key) || empty($key)) {
				continue;
			}

			$map->put($key, $value);
		}

		return $map;
	}
}
