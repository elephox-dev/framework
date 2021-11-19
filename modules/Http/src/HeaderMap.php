<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ObjectMap;
use Elephox\Collection\OffsetNotFoundException;

/**
 * @extends ObjectMap<Contract\HeaderName, array<int, string>|string>
 */
class HeaderMap extends ObjectMap implements Contract\HeaderMap
{
	/**
	 * @param non-empty-string $name
	 * @return Contract\HeaderName
	 */
	public static function parseHeaderName(string $name): Contract\HeaderName
	{
		/**
		 * @var Contract\HeaderName|null $headerName
		 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
		 */
		$headerName = HeaderName::tryFrom($name);
		if ($headerName === null) {
			$headerName = new CustomHeaderName($name);
		}

		return $headerName;
	}

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

			$headerName = self::parseHeaderName($name);
			if ($headerName->canBeDuplicate()) {
				if (is_string($value)) {
					$values = [$value];
				} else if (is_array($value)) {
					$values = array_values($value);
				} else {
					throw new InvalidHeaderTypeException($value);
				}
			} else if (is_string($value)) {
				$values = $value;
			} else if (is_array($value) && !empty($value)) {
				$values = (string)$value[0];
			} else {
				throw new InvalidHeaderTypeException($value);
			}
			/** @var array<int, string>|string $values */

			$map->put($headerName, $values);
		}

		return $map;
	}

	/**
	 * @param non-empty-string|Contract\HeaderName $key
	 * @param array<int, string>|string $value
	 *
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	public function put(mixed $key, mixed $value): void
	{
		if (!$key instanceof Contract\HeaderName) {
			$key = self::parseHeaderName($key);
		}

		if (is_array($value) && !$key->canBeDuplicate()) {
			parent::put($key, $value[0]);
		} else if (!is_array($value) && $key->canBeDuplicate()) {
			parent::put($key, [$value]);
		} else {
			parent::put($key, $value);
		}
	}

	/**
	 * @param non-empty-string|Contract\HeaderName $key
	 *
	 * @return array<int, string>|string
	 *
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	public function get(mixed $key): array|string
	{
		if (!$key instanceof Contract\HeaderName) {
			$key = self::parseHeaderName($key);
		}

		$obj = $this->firstKey(static fn (array|string $value, Contract\HeaderName $name) => $name->getValue() === $key->getValue());
		if ($obj === null) {
			throw new OffsetNotFoundException($key->getValue());
		}

		return parent::get($obj);
	}

	public function asArray(): array
	{
		$headers = [];

		/**
		 * @var Contract\HeaderName $key
		 */
		foreach ($this as $key => $value) {
			$headers[$key->getValue()] = $value;
		}

		return $headers;
	}
}
