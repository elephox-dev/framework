<?php
declare(strict_types=1);

namespace Philly\Http;

use LogicException;

class RequestHeaderMap extends HeaderMap implements Contract\RequestHeaderMap
{
	public static function fromArray(array $headers): self
	{
		$map = parent::fromArray($headers);

		/** @psalm-suppress UnusedClosureParam */
		if ($map->any(static fn(array $value, HeaderName $name) => $name->isOnlyResponse())) {
			throw new LogicException("Cannot set response headers in request header map.");
		}

		return new self($map);
	}

	private function __construct(HeaderMap $map)
	{
		parent::__construct();

		$this->map = $map->map;
	}
}
