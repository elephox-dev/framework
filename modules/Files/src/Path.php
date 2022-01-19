<?php
declare(strict_types=1);

namespace Elephox\Files;

class Path
{
	/**
	 * @throws \Safe\Exceptions\PcreException
	 */
	public static function join(string... $args): string
	{
		$parts = array_filter($args, static fn (string $arg) => $arg !== '');
		$path = implode(DIRECTORY_SEPARATOR, $parts);

		/** @var string */
		return \Safe\preg_replace('#' . DIRECTORY_SEPARATOR . '+#', DIRECTORY_SEPARATOR, $path);
	}

	/**
	 * @throws \Safe\Exceptions\PcreException
	 */
	public static function isRoot(string $path): bool
	{
		return $path === '\\' ||
			$path === '/' ||
			$path === dirname($path) ||
			\Safe\preg_match("/^\w:\\\\$/", $path) === 1;
	}
}
