<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ObjectMap;
use Elephox\Collection\ArrayList;
use Elephox\Collection\OffsetNotFoundException;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

/**
 * @extends ObjectMap<Contract\HeaderName, ArrayList<string>>
 */
class HeaderMap extends ObjectMap implements Contract\HeaderMap
{
	/**
	 * @param string $name
	 * @return Contract\HeaderName
	 */
	public static function parseHeaderName(string $name): Contract\HeaderName
	{
		if (empty($name)) {
			throw new InvalidArgumentException('Header name cannot be empty');
		}

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

	protected static function fromArray(iterable $headers): self
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
			if (is_string($value)) {
				$values = ArrayList::fromValue($value);
			} else if (is_array($value)) {
				$values = ArrayList::fromArray($value);
			} else {
				throw new InvalidHeaderTypeException($value);
			}
			/** @var ArrayList<string> $values */

			$map->put($headerName, $values);
		}

		return $map;
	}

	/**
	 * @param string|Contract\HeaderName $key
	 * @return bool
	 *
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	public function has(mixed $key): bool
	{
		if (!$key instanceof Contract\HeaderName) {
			$key = self::parseHeaderName($key);
		}

		return parent::has($key);
	}

	/**
	 * @param string|Contract\HeaderName $key
	 * @param iterable<string>|string $value
	 *
	 * @psalm-suppress MoreSpecificImplementedParamType
	 * @psalm-suppress DocblockTypeContradiction
	 */
	public function put(mixed $key, mixed $value): void
	{
		if (!$key instanceof Contract\HeaderName) {
			$key = self::parseHeaderName($key);
		}

		if (!is_array($value)) {
			if (!is_string($value)) {
				throw new InvalidHeaderTypeException($value);
			}

			parent::put($key, ArrayList::fromValue($value));
		} else {
			parent::put($key, ArrayList::fromArray($value));
		}
	}

	/**
	 * @param string|Contract\HeaderName $key
	 *
	 * @return ArrayList<string>
	 *
	 * @psalm-suppress MoreSpecificImplementedParamType
	 */
	public function get(mixed $key): ArrayList
	{
		if (!$key instanceof Contract\HeaderName) {
			$key = self::parseHeaderName($key);
		}

		$obj = $this->firstKey(static fn (Contract\HeaderName $name) => $name->getValue() === $key->getValue());
		if ($obj === null) {
			throw new OffsetNotFoundException($key->getValue());
		}

		return parent::get($obj);
	}

	/**
	 * @return array<non-empty-string, list<string>>
	 */
	public function asArray(): array
	{
		$headers = [];

		foreach ($this as $key => $value) {
			$headerName = $key->getValue();

			/** @var list<string> $headerValue */
			$headerValue = $value->asArray();

			$headers[$headerName] = $headerValue;
		}

		return $headers;
	}

	#[Pure] public function asRequestHeaders(): RequestHeaderMap
	{
		return RequestHeaderMap::fromArray($this);
	}

	#[Pure] public function asResponseHeaders(): ResponseHeaderMap
	{
		return ResponseHeaderMap::fromArray($this);
	}
}
