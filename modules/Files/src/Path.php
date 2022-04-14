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

		return (string) preg_replace('#[/\\\\]{2,}#', DIRECTORY_SEPARATOR, $path);
	}

	#[Pure]
	public static function canonicalize(string $path): string
	{
		return (string) preg_replace('#[/\\\\]#', DIRECTORY_SEPARATOR, $path);
	}

	#[Pure]
	public static function relativeTo(string $source, string $target): string
	{
		$sourceParts = explode(DIRECTORY_SEPARATOR, self::canonicalize($source));
		$targetParts = explode(DIRECTORY_SEPARATOR, self::canonicalize($target));

		$relativeParts = $targetParts;
		foreach ($sourceParts as $depth => $part) {
			if (isset($targetParts[$depth]) && $part === $targetParts[$depth]) {
				array_shift($relativeParts);

				continue;
			}

			$remaining = count($sourceParts) - $depth;
			if ($remaining <= 1) {
				$relativeParts[0] = '.' . DIRECTORY_SEPARATOR . $relativeParts[0];

				break;
			}

			$padLength = (count($relativeParts) + $remaining) * -1;
			$relativeParts = array_pad($relativeParts, $padLength, '..');

			break;
		}

		return implode(DIRECTORY_SEPARATOR, $relativeParts);
	}

	#[Pure]
	public static function isRoot(string $path): bool
	{
		return $path === '\\' ||
			$path === '/' ||
			$path === dirname($path) ||
			preg_match("/^\w:\\\\$/", $path) === 1;
	}

	#[Pure]
	public static function isRooted(string $path): bool
	{
		return $path[0] === '/' || preg_match("/^\w:\\\\/", $path) === 1;
	}
}
