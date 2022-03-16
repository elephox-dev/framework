<?php
declare(strict_types=1);

namespace Elephox\Host\Contract;

use Elephox\DI\Contract\ServiceProvider;

interface Host
{
	public function getServices(): ServiceProvider;
}
