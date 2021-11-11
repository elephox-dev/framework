<?php
declare(strict_types=1);

namespace Philly\Http;

use Philly\Collection\ArrayMap;

/**
 * @extends ArrayMap<non-empty-string, array<int, string>>
 */
class HeaderMap extends ArrayMap implements Contract\HeaderMap
{
	protected static function fromArray(array $headers): self
	{
		$map = new self();

		/**
		 * @var mixed $value
		 */
		foreach ($headers as $name => $value) {
			if (!is_string($name)) {
				throw new InvalidHeaderNameTypeException($name);
			}

			if (empty($name)) {
				throw new InvalidHeaderNameException($name);
			}

			/**
			 * @var \Philly\Http\Contract\HeaderName|null $headerName
			 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
			 */
			$headerName = HeaderName::tryFrom($name);
			if ($headerName === null) {
				$headerName = new CustomHeaderName($name);
			}

			if (is_string($value)) {
				$values = [$value];
			} else if (is_array($value)) {
				$values = array_values($value);
			} else {
				throw new InvalidHeaderTypeException($value);
			}
			/** @var array<int, string> $values */

			$map->put($headerName, $values);
		}

		return $map;
	}

	/**
	 * @param non-empty-string|Contract\HeaderName $key
	 * @param array<int, string> $value
	 *
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	public function put(mixed $key, mixed $value): void
	{
		if ($key instanceof Contract\HeaderName) {
			parent::put($key->getValue(), $value);
		} else {
			parent::put($key, $value);
		}
	}

	/**
	 * @param non-empty-string|Contract\HeaderName $key
	 * @return array<int, string>
	 *
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	public function get(mixed $key): mixed
	{
		if ($key instanceof Contract\HeaderName) {
			return parent::get($key->getValue());
		}

		return parent::get($key);
	}
}
