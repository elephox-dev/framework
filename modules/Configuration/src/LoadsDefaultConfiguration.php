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
		$envName = $this->getEnvironment()->getEnvironmentName();

		$this->getEnvironment()->loadFromEnvFile($envName);
		$this->getEnvironment()->loadFromEnvFile($envName, true);
	}

	protected function loadConfigFile(): void
	{
		$this->getConfigurationBuilder()->add(new JsonFileConfigurationSource(
			$this->getEnvironment()
				->getConfig()
				->getFile('config.json')
				->getPath(),
			true,
		));

		$this->getConfigurationBuilder()->add(new JsonFileConfigurationSource(
			$this->getEnvironment()
				->getConfig()
				->getFile('config.local.json')
				->getPath(),
			true,
		));
	}

	protected function loadEnvironmentConfigFile(): void
	{
		$this->getConfigurationBuilder()->add(new JsonFileConfigurationSource(
			$this->getEnvironment()
				->getRoot()
				->getFile("config.{$this->getEnvironment()->getEnvironmentName()}.json")
				->getPath(),
			true,
		));

		$this->getConfigurationBuilder()->add(new JsonFileConfigurationSource(
			$this->getEnvironment()
				->getRoot()
				->getFile("config.{$this->getEnvironment()->getEnvironmentName()}.local.json")
				->getPath(),
			true,
		));
	}
}
