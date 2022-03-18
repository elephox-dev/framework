<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Files\Directory;
use Elephox\Host\Contract\HostEnvironment;
use RuntimeException;

class GlobalHostEnvironment extends GlobalEnvironment implements HostEnvironment
{
	public function getRootDirectory(): Directory
	{
		if (defined('APP_ROOT')) {
			return new Directory((string) APP_ROOT);
		}

		$cwd = getcwd();
		if (!$cwd) {
			throw new RuntimeException('Cannot get current working directory');
		}

		return new Directory($cwd);
	}
}
