<?php
declare(strict_types=1);

namespace Elephox\Web\Routing\Contract;

interface ComposerAutoloaderInit
{
	public static function getLoader(): ComposerClassLoader;
}
