<?php
declare(strict_types=1);

namespace Elephox\Development;

use Elephox\Configuration\Json\JsonFileConfigurationSource;
use Elephox\Console\ConsoleApplicationBuilder;

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
