<?php

namespace Philly\Http;

use LogicException;

class ResponseHeaderMap extends HeaderMap implements Contract\ResponseHeaderMap
{
	public static function fromArray(array $headers): self
	{
		$map = parent::fromArray($headers);

		/** @psalm-suppress UnusedClosureParam */
		if ($map->any(static fn(array $value, HeaderName $name) => $name->isOnlyRequest())) {
			throw new LogicException("Cannot set request headers in response header map.");
		}

		return new self($map);
	}

	private function __construct(HeaderMap $map)
	{
		parent::__construct();

		$this->map = $map->map;
	}
}
