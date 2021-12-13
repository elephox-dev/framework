<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Pure;

class RequestHeaderMap extends HeaderMap implements Contract\RequestHeaderMap
{
	#[Pure] public static function fromArray(iterable $headers): self
	{
		$map = parent::fromArray($headers);

		$requestHeaderMap = new self();

		/** @psalm-suppress ImpurePropertyAssignment */
		$requestHeaderMap->map = $map->map;

		return $requestHeaderMap;
	}
}
