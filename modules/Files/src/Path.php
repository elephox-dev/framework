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
	public static function canonicalize(string $path, string $separator = DIRECTORY_SEPARATOR): string
	{
		return (string) preg_replace('#[/\\\\]#', $separator, $path);
	}

	#[Pure]
	public static function relativeTo(string $source, string $target): string
	{
		$source = self::canonicalize($source);
		$target = self::canonicalize($target);

		$fromParts = explode(DIRECTORY_SEPARATOR, $source);
		$toParts = explode(DIRECTORY_SEPARATOR, $target);

		while (($fromParts[0] ?? null) === ($toParts[0] ?? null)) {
			array_shift($fromParts);
			array_shift($toParts);
		}

		if (empty($toParts)) {
			array_unshift($toParts, '');
		}

		if (count($fromParts) === 0 || $fromParts[0] === '') {
			array_unshift($toParts, '.');
		} else {
			$toParts = array_pad($toParts, -1 * (count(array_filter($fromParts)) + count($toParts)), '..');
		}

		return implode(DIRECTORY_SEPARATOR, $toParts);
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
