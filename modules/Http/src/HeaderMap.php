<?php

namespace Philly\Http;

use InvalidArgumentException;
use Philly\Collection\GenericWeakMap;

/**
 * @extends \Philly\Collection\GenericWeakMap<\Philly\Http\HeaderName, array<int, string>>
 */
class HeaderMap extends GenericWeakMap implements Contract\HeaderMap
{
	public static function fromArray(array $headers): self
	{
		$map = new self();

		/** @var mixed $value */
		foreach ($headers as $name => $value) {
			if (!is_string($name)) {
				throw new InvalidHeaderNameTypeException($name);
			}

			if (is_string($value)) {
				$value = [$value];
			} else if (is_array($value)) {
				$value = array_values(
					array_map(
						static fn($val) => is_string($val) ?
							$val :
							throw new InvalidHeaderTypeException($val),
						$value
					)
				);
			} else {
				throw new InvalidHeaderTypeException($value);
			}

			/**
			 * @var \Philly\Http\HeaderName|null $headerName
			 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
			 */
			$headerName = HeaderName::tryFrom($name);
			if ($headerName === null) {
				throw new InvalidHeaderNameException($name);
			}

			$map->put($headerName, $value);
		}

		return $map;
	}

	public function asArray(): array
	{
		$arr = [];

		/**
		 * @var \Philly\Http\HeaderName $name
		 * @var array<int, string> $values
		 */
		foreach ($this->map as $name => $values) {
			/**
			 * @var string $key
			 * @psalm-suppress UndefinedPropertyFetch Until vimeo/psalm#6468 is fixed
			 */
			$key = $name->value;

			$arr[$key] = $values;
		}
		return $arr;
	}
}
