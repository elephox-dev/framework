<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

interface ComposerAutoloaderInit
{
	public static function getLoader(): ComposerClassLoader;
}
