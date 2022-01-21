<?php
declare(strict_types=1);

namespace Elephox\Files;

use JetBrains\PhpStorm\Pure;

class Path
{
	#[Pure]
	public static function join(string... $args): string
	{
		$parts = array_filter($args, static fn (string $arg) => $arg !== '');
		$path = implode(DIRECTORY_SEPARATOR, $parts);
		return preg_replace('#' . DIRECTORY_SEPARATOR . '+#', DIRECTORY_SEPARATOR, $path);
	}

	#[Pure]
	public static function isRoot(string $path): bool
	{
		return $path === '\\' ||
			$path === '/' ||
			$path === dirname($path) ||
			preg_match("/^\w:\\\\$/", $path) === 1;
	}
}
