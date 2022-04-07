<?php
declare(strict_types=1);

namespace Elephox\Files;

use JetBrains\PhpStorm\Pure;

class Path
{
	#[Pure]
	public static function join(string $arg, string ...$args): string
	{
		array_unshift($args, $arg);
		$parts = array_filter($args, static fn (string $arg) => $arg !== '');
		$path = implode(DIRECTORY_SEPARATOR, $parts);

		return (string) preg_replace('#' . DIRECTORY_SEPARATOR . '+#', DIRECTORY_SEPARATOR, $path);
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
