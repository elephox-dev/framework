<?php
declare(strict_types=1);

namespace Elephox\Configuration;

use Elephox\Configuration\Json\JsonFileConfigurationSource;

trait LoadsDefaultConfiguration
{
	abstract protected function getEnvironment(): Contract\Environment;

	abstract protected function getConfigurationBuilder(): Contract\ConfigurationBuilder;

	protected function loadDotEnvFile(): void
	{
		$this->getEnvironment()->loadFromEnvFile();
		$this->getEnvironment()->loadFromEnvFile(local: true);
	}

	protected function loadEnvironmentDotEnvFile(): void
	{
		$envName = $this->getEnvironment()->environmentName();

		$this->getEnvironment()->loadFromEnvFile($envName);
		$this->getEnvironment()->loadFromEnvFile($envName, true);
	}

	protected function loadConfigFile(): void
	{
		$this->getConfigurationBuilder()->add(new JsonFileConfigurationSource(
			$this->getEnvironment()
				->config()
				->file('config.json')
				->path(),
			true,
		));

		$this->getConfigurationBuilder()->add(new JsonFileConfigurationSource(
			$this->getEnvironment()
				->config()
				->file('config.local.json')
				->path(),
			true,
		));
	}

	protected function loadEnvironmentConfigFile(): void
	{
		$this->getConfigurationBuilder()->add(new JsonFileConfigurationSource(
			$this->getEnvironment()
				->root()
				->file("config.{$this->getEnvironment()->environmentName()}.json")
				->path(),
			true,
		));

		$this->getConfigurationBuilder()->add(new JsonFileConfigurationSource(
			$this->getEnvironment()
				->root()
				->file("config.{$this->getEnvironment()->environmentName()}.local.json")
				->path(),
			true,
		));
	}
}
