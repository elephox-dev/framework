<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\GlobalEnvironment;
use Elephox\Files\Directory;
use RuntimeException;

class GlobalHostEnvironment extends GlobalEnvironment implements Contract\HostEnvironment
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
