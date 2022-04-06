<?php
declare(strict_types=1);

namespace Elephox\Development;

use Elephox\Configuration\ConfigurationManager;
use Elephox\Configuration\Json\JsonFileConfigurationSource;
use Elephox\Console\Command\CommandCollection;
use Elephox\Console\ConsoleApplicationBuilder;
use Elephox\Console\GlobalConsoleEnvironment;
use Elephox\DI\ServiceCollection;

class DevConsoleApplicationBuilder extends ConsoleApplicationBuilder
{
	protected function registerDefaultConfig(): void
	{
		$this->configuration->add(new JsonFileConfigurationSource(
			$this->environment
				->getRootDirectory()
				->getFile("config.{$this->environment->getEnvironmentName()}.json")
				->getPath(),
			true
		));

		$this->configuration->add(new JsonFileConfigurationSource(
			$this->environment
				->getRootDirectory()
				->getFile("config.local.json")
				->getPath(),
			true
		));
	}
}
