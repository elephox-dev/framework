<?php
declare(strict_types=1);

namespace Elephox\Core\Contract;

use Elephox\DI\Contract\Container;

interface Registrar
{
	public function registerAll(Container $container): void;
}
