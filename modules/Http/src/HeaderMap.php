<?php
declare(strict_types=1);

namespace Philly\Http;

use Philly\Collection\ArrayMap;

/**
 * @extends ArrayMap<string|Contract\HeaderName, array<int, string>>
 */
class HeaderMap extends ArrayMap implements Contract\HeaderMap
{
	protected static function fromArray(array $headers): self
	{
		$map = new self();

		/**
		 * @var array-key $name
		 * @var mixed $value
		 */
		foreach ($headers as $name => $value) {
			if (!is_string($name)) {
				throw new InvalidHeaderNameTypeException($name);
			}

			if (is_string($value)) {
				$value = [$value];
			} else if (is_array($value)) {
				$value = array_values($value);
			} else {
				throw new InvalidHeaderTypeException($value);
			}

			/**
			 * @var \Philly\Http\Contract\HeaderName|null $headerName
			 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
			 */
			$headerName = HeaderName::tryFrom($name);
			if ($headerName === null) {
				$headerName = new CustomHeaderName($name);
			}

			$map->put($headerName, $value);
		}

		return $map;
	}

	public function put(mixed $key, mixed $value): void
	{
		if ($key instanceof Contract\HeaderName) {
			parent::put($key->getValue(), $value);
		} else {
			parent::put($key, $value);
		}
	}

	public function get(mixed $key): mixed
	{
		if ($key instanceof Contract\HeaderName) {
			return parent::get($key->getValue());
		}

		return parent::get($key);
	}
}
