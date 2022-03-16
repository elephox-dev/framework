<?php
declare(strict_types=1);

namespace Elephox\Host\Contract;

use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\DI\Contract\ServiceProvider;

interface ApplicationBuilder
{
	public function getConfiguration(): ConfigurationBuilder&ConfigurationRoot;

	public function getEnvironment(): HostEnvironment;

	public function getServices(): ServiceProvider;
}
