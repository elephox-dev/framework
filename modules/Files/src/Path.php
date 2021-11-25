<?php
declare(strict_types=1);

namespace Elephox\Files;

class Path
{
	public static function join(string... $args): string
	{
		$paths = array();

		foreach ($args as $arg) {
			if ($arg !== '') {
				$paths[] = $arg;
			}
		}

		return preg_replace('#' . DIRECTORY_SEPARATOR . '+#', DIRECTORY_SEPARATOR, implode(DIRECTORY_SEPARATOR, $paths));
	}
}
