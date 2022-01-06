<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Pure;

class RequestHeaderMap extends HeaderMap implements Contract\RequestHeaderMap
{
	public static function fromIterable(iterable $headers): self
	{
		$map = parent::fromIterable($headers);

		$requestHeaderMap = new self();

		/** @psalm-suppress ImpurePropertyAssignment */
		$requestHeaderMap->map = $map->map;

		return $requestHeaderMap;
	}
}
