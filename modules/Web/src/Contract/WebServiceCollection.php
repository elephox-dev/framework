<?php
declare(strict_types=1);

namespace Elephox\Web\Contract;

use Elephox\DI\Contract\ServiceCollection;

interface WebServiceCollection extends ServiceCollection
{
	public function addDoctrine(?callable $configurator = null): WebServiceCollection;

	public function addWhoops(?callable $configurator = null): WebServiceCollection;
}
