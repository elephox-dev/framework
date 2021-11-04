<?php

namespace Philly\Http;

use InvalidArgumentException;
use Philly\Collection\HashMap;

/**
 * @extends \Philly\Collection\HashMap<\Philly\Http\HeaderName, array<int, string>>
 */
class HeaderMap extends HashMap implements Contract\HeaderMap
{
	public static function fromArray(array $headers): self
	{
		$map = new self();

		/** @var mixed $value */
		foreach ($headers as $name => $value) {
			if (is_string($value)) {
				$value = [$value];
			} else if (is_array($value)) {
				$value = array_values(
					array_map(
						static fn($val) => is_string($val) ?
							$val :
							throw new InvalidArgumentException("Header value array can only contain string values"),
						$value
					)
				);
			} else {
				throw new InvalidArgumentException("Header value must be an array or string, " . gettype($value) . " given");
			}

			if (is_string($name)) {
				$map->put(HeaderName::fromString($name), $value);
			} else {
				throw new InvalidArgumentException("Header name must be a string");
			}
		}

		return $map;
	}
}
