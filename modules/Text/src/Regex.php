<?php
declare(strict_types=1);

namespace Elephox\Text;

use InvalidArgumentException;
use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;

class Regex
{
	/**
	 * @param string $pattern
	 * @param string $subject
	 * @param int $limit
	 * @return ArrayList<string>
	 */
	public static function split(string $pattern, string $subject, int $limit = -1): ArrayList
	{
		$parts = preg_split($pattern, $subject, $limit);
		if ($parts === false) {
			throw new InvalidArgumentException('An error occurred while splitting: ' . preg_last_error_msg());
		}

		/** @var ArrayList<string> */
		return ArrayList::fromArray($parts);
	}

	/**
	 * @param string $pattern
	 * @param string $subject
	 * @return ArrayMap<int|string, string>
	 */
	public static function match(string $pattern, string $subject): ArrayMap
	{
		$matches = [];
		if (preg_match($pattern, $subject, $matches) === false) {
			throw new InvalidArgumentException('An error occurred while matching: ' . preg_last_error_msg());
		}

		/** @var ArrayMap<int|string, string> */
		return ArrayMap::fromIterable($matches);
	}

	public static function matches(string $pattern, string $subject): bool
	{
		return preg_match($pattern, $subject) === 1;
	}
}
