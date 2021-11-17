<?php
declare(strict_types=1);

namespace Elephox\Http;

use InvalidArgumentException;
use LogicException;
use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use Elephox\Collection\KeyValuePair;
use Elephox\Text\Regex;

class ResponseHeaderMap extends HeaderMap implements Contract\ResponseHeaderMap
{
	public static function fromString(string $headers): self
	{
		$rows = Regex::split('/\n/', $headers);

		/** @var ArrayList<KeyValuePair<string, string>> $headerRows */
		$headerKeyValueList = $rows
			->where(static fn (string $row) => trim($row) !== '')
			->map(static function (string $row): KeyValuePair {
				if (!str_contains($row, ':')) {
					throw new InvalidArgumentException("Invalid header row: $row");
				}

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

		$responseHeaderMap = new self();
		$responseHeaderMap->values = $map->values;

		return $responseHeaderMap;
	}
}
