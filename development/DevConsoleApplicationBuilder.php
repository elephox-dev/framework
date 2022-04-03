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
	public static function create(): self
	{
		$configuration = new ConfigurationManager();
		$environment = new GlobalConsoleEnvironment();
		$services = new ServiceCollection();
		$commands = new CommandCollection($services->resolver());

		return new self(
			$configuration,
			$environment,
			$services,
			$commands,
		);
	}

	protected function registerDefaultConfig(): self
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

		return $this;
	}
}
