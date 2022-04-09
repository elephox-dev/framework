<?php
declare(strict_types=1);

namespace Elephox\Autoloading\Composer\Contract;

use Composer\Autoload\ClassLoader;

interface ComposerAutoloaderInit
{
	public static function getLoader(): ClassLoader;
}
