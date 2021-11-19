<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Collection\KeyValuePair;
use Elephox\Text\Regex;
use InvalidArgumentException;

class ResponseHeaderMap extends HeaderMap implements Contract\ResponseHeaderMap
{
	public static function fromString(string $headers): self
	{
		$rows = Regex::split('/\n/', $headers);

		$headerKeyValueList = $rows
			->where(static fn (string $row) => trim($row) !== '')
			->map(static function (string $row): KeyValuePair {
				if (!str_contains($row, ':')) {
					throw new InvalidArgumentException("Invalid header row: $row");
				}

				[$name, $value] = explode(':', $row, 2);
				return new KeyValuePair($name, trim($value));
			});

		/**
		 * @var ArrayMap<array-key, array<int, string>|string> $headers
		 * @psalm-suppress InvalidArgument The generic types are subtypes of the expected ones.
		 */
		$headerMap = ArrayMap::fromKeyValuePairList($headerKeyValueList);

		return self::fromArray($headerMap->asArray());
	}

	public static function fromArray(array $headers): self
	{
		$map = parent::fromArray($headers);

		$responseHeaderMap = new self();
		$responseHeaderMap->map = $map->map;

		return $responseHeaderMap;
	}
}
