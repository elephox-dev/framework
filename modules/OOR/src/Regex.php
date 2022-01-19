<?php
declare(strict_types=1);

namespace Elephox\OOR;

use InvalidArgumentException;
use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;
use JetBrains\PhpStorm\Pure;

class Regex
{
	/**
	 * @param string $pattern
	 * @param string $subject
	 * @param int $limit
	 * @return ArrayList<string>
	 * @throws \Safe\Exceptions\PcreException
	 */
	public static function split(string $pattern, string $subject, int $limit = -1): ArrayList
	{
		$parts = \Safe\preg_split($pattern, $subject, $limit);

		/** @var ArrayList<string> */
		return ArrayList::from($parts);
	}

	/**
	 * @param string $pattern
	 * @param string $subject
	 * @return ArrayMap<int|string, string>
	 * @throws \Safe\Exceptions\PcreException
	 */
	public static function match(string $pattern, string $subject): ArrayMap
	{
		\Safe\preg_match($pattern, $subject, $matches);
		$matches ??= [];

		/** @var ArrayMap<array-key, string> */
		return ArrayMap::from($matches);
	}

	/**
	 * @throws \Safe\Exceptions\PcreException
	 */
	public static function matches(string $pattern, string $subject): bool
	{
		return \Safe\preg_match($pattern, $subject) === 1;
	}
}
