<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\DI\Contract\ServiceProvider;
use Elephox\Host\Contract\ApplicationBuilder;
use Elephox\Host\Contract\Host;
use Elephox\Host\Contract\HostEnvironment;

class WebApplication implements Host, ApplicationBuilder
{
	public function getConfiguration(): ConfigurationBuilder&ConfigurationRoot
	{
		// TODO: Implement getConfiguration() method.
	}

	public function getEnvironment(): HostEnvironment
	{
		// TODO: Implement getEnvironment() method.
	}

	public function getServices(): ServiceProvider
	{
		// TODO: Implement getServices() method.
	}
}
