<?php
declare(strict_types=1);

namespace Philly\Http;

use InvalidArgumentException;
use LogicException;
use Philly\Collection\ArrayList;
use Philly\Collection\ArrayMap;
use Philly\Collection\KeyValuePair;
use Philly\Text\Regex;

class ResponseHeaderMap extends HeaderMap implements Contract\ResponseHeaderMap
{
	public static function fromString(string $headers): self
	{
		$rows = Regex::split('/\n/', $headers);
		$rows->pop(); // remove last empty row

		/** @var ArrayList<KeyValuePair<string, string>> $headerRows */
		$headerKeyValueList = $rows->map(static function (string $row): KeyValuePair {
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
