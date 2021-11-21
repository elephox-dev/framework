<?php
declare(strict_types=1);

namespace Elephox\Text;

use InvalidArgumentException;
use Elephox\Collection\ArrayList;

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
		$parts = preg_split($pattern, $subject, $limit,);
		if ($parts === false) {
			throw new InvalidArgumentException('An error occurred while splitting: ' . preg_last_error_msg());
		}

		/** @var ArrayList<string> */
		return ArrayList::fromArray($parts);
	}
}
