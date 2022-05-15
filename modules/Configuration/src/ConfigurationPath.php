<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\OOR\Arr;
use Elephox\OOR\Str;
use Stringable;

final class ConfigurationPath
{
	public const SECTION_SEPARATOR = ':';

	public static function getSectionKey(string|Stringable $path): Str
	{
		/** @var string $val */
		$val = self::getSectionKeys($path)->end();

		return Str::wrap($val);
	}

	public static function getSectionKeys(string|Stringable $path): Arr
	{
		return Str::wrap($path)->explode(self::SECTION_SEPARATOR)->filter();
	}

	public static function getChildKeys(array|Arr $data, string|Stringable $path): Arr
	{
		$keys = self::getSectionKeys($path);

		return self::getChildKeysRecursive(Arr::wrap($data), $keys->filter()->reverse());
	}

	private static function getChildKeysRecursive(Arr $data, Arr $keys): Arr
	{
		/** @var string $key */
		$key = $keys->pop();
		if ($keys->isEmpty()) {
			return Arr::wrap($data[$key])->keys();
		}

		return self::getChildKeysRecursive(Arr::wrap($data[$key]), $keys);
	}

	public static function appendKey(string|Stringable $path, string|Stringable $key): Str
	{
		return Str::implode(self::SECTION_SEPARATOR, (string) $path, (string) $key);
	}
}
