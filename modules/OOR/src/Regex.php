<?php
declare(strict_types=1);

namespace Elephox\OOR;

use InvalidArgumentException;
use Elephox\Collection\ArrayList;
use Elephox\Collection\ArrayMap;

class Regex
{
	/**
	 * @return ArrayList<string>
	 *
	 * @param string $pattern
	 * @param string $subject
	 * @param int $limit
	 */
	public static function split(string $pattern, string $subject, int $limit = -1): ArrayList
	{
		$parts = preg_split($pattern, $subject, $limit);
		if ($parts === false) {
			throw new InvalidArgumentException('An error occurred while splitting: ' . preg_last_error_msg());
		}

		/** @var ArrayList<string> */
		return ArrayList::from($parts);
	}

	/**
	 * @return ArrayMap<int|string, string>
	 *
	 * @param string $pattern
	 * @param string $subject
	 */
	public static function match(string $pattern, string $subject): ArrayMap
	{
		$matches = [];
		if (preg_match($pattern, $subject, $matches) === false) {
			throw new InvalidArgumentException('An error occurred while matching: ' . preg_last_error_msg());
		}

		/** @var ArrayMap<array-key, string> */
		return ArrayMap::from($matches);
	}

	public static function matches(string $pattern, string $subject): bool
	{
		return preg_match($pattern, $subject) === 1;
	}

	public static function specificity(string $pattern, string $subject): float
	{
		$maxScore = strlen($subject);
		if (!self::matches($pattern, $subject)) {
			return $maxScore;
		}

		$score = 0;
		for ($i = 0, $iMax = $maxScore; $i < $iMax; $i++) {
			$modifiedSubject = substr_replace($subject, '', $i, 1);

			if (self::matches($pattern, $modifiedSubject)) {
				$score++;
			}
		}

		if ($score === 0) {
			return 0;
		}

		return 1 - ($score / $maxScore);
	}

	public static function escape(string $pattern): string
	{
		return preg_quote($pattern, '/');
	}
}
