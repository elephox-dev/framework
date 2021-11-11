<?php
declare(strict_types=1);

namespace Elephox\Http;

class RequestHeaderMap extends HeaderMap implements Contract\RequestHeaderMap
{
	public static function fromArray(array $headers): self
	{
		$map = parent::fromArray($headers);

		$requestHeaderMap = new self();
		$requestHeaderMap->values = $map->values;

		return $requestHeaderMap;
	}
}
