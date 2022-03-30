<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Configuration\Json\JsonFileConfigurationSource;
use Elephox\Console\Contract\ConsoleEnvironment;
use Elephox\DI\Contract\ServiceCollection;

class ConsoleApplicationBuilder
{
	public function __construct(
		public readonly ConfigurationBuilder&ConfigurationRoot $configuration,
		public readonly ConsoleEnvironment $environment,
		public readonly ServiceCollection $services,
	)
	{
		$this->registerDefaultConfig();
		$this->setDebugFromConfig();
	}

	public function registerDefaultConfig(): void
	{
		$this->configuration->add(new JsonFileConfigurationSource(
			$this->environment
				->getRootDirectory()
				->getFile("config.json")
				->getPath()
		));
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

	public function setDebugFromConfig(): void
	{
		if ($this->configuration->hasSection("env:debug")) {
			$this->environment->offsetSet('APP_DEBUG', (bool)$this->configuration['env:debug']);
		}
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
