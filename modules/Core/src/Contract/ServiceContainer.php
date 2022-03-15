<?php
declare(strict_types=1);

namespace Elephox\Core\Contract;

use Elephox\DI\Contract\Container;

interface ServiceContainer extends Container
{
	public function seal(): void;
}
