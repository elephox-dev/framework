<?php
declare(strict_types=1);

namespace Elephox\Autoloading\Composer\Contract;

interface ComposerAutoloaderInit
{
	public static function getLoader(): ComposerClassLoader;
}
