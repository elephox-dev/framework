<?php

namespace Elephox\Core;

use Elephox\Core\Contract\App;
use Elephox\Core\Contract\AppBuilder;

class SimpleWebApp implements App
{
	public static function createBuilder(): AppBuilder
	{
		return new SimpleWebAppBuilder(Core::instance()->getContainer());
	}
}
