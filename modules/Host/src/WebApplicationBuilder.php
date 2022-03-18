<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Host\Contract\WebServiceCollection;
use Elephox\Host\Contract\WebHostEnvironment;

class WebApplicationBuilder
{
	public function __construct(
		public readonly ConfigurationBuilder&ConfigurationRoot $configuration,
		public readonly WebHostEnvironment $environment,
		public readonly WebServiceCollection $services,
	)
	{
	}

	public function build(): WebApplication
	{
		return new WebApplication(
			$this->environment,
			$this->services,
			$this->configuration->build(),
		);
	}
}
