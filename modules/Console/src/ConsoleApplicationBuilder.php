<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Host\Contract\HostEnvironment;

class ConsoleApplicationBuilder
{
	public function __construct(
		public readonly ConfigurationBuilder&ConfigurationRoot $configuration,
		public readonly HostEnvironment $environment,
		public readonly ServiceCollection $services,
	)
	{
	}

	public function build(): ConsoleApplication
	{
		return new ConsoleApplication(
			$this->services,
			$this->environment,
			$this->configuration->build(),
		);
	}
}
