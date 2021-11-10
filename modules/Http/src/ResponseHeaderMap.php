<?php
declare(strict_types=1);

namespace Philly\Http;

use LogicException;
use Philly\Collection\ArrayList;
use Philly\Collection\ArrayMap;
use Philly\Collection\KeyValuePair;
use Philly\Text\Regex;

class ResponseHeaderMap extends HeaderMap implements Contract\ResponseHeaderMap
{
	public static function empty(): Contract\ResponseHeaderMap
	{
		return new self(parent::fromArray([]));
	}

	public static function fromString(string $headers): self
	{
		/** @var ArrayList<KeyValuePair<string, string>> $headerRows */
		$headerKeyValueList = Regex::split("[\r\n]", $headers)
			->map(static function (string $row): KeyValuePair {
				[$name, $value] = explode(':', $row, 2);
				return new KeyValuePair($name, trim($value));
			});
		/** @psalm-suppress InvalidArgument The generic types are subtypes of the expected ones. */
		$headerMap = ArrayMap::fromKeyValuePairList($headerKeyValueList);

		return self::fromArray($headerMap->asArray());
	}

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
		$this->map = $map->map;
	}
}
