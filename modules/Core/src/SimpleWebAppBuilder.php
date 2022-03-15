<?php
declare(strict_types=1);

namespace Elephox\Core;

use Elephox\Core\Contract\App;
use Elephox\Support\TransparentGetterSetter;

/**
 * @extends AppBuilder<SimpleWebApp, Contract\ServiceContainer>
 */
class SimpleWebAppBuilder extends AppBuilder
{
	use TransparentGetterSetter;

	public function build(): App
	{
		return new SimpleWebApp();
	}
}
