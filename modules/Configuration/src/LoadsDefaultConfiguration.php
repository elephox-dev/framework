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
		$envFile = $this->getEnvironment()->getDotEnvFileName();
		$this->getEnvironment()->loadFromEnvFile($envFile);

		$localEnvFile = $this->getEnvironment()->getDotEnvFileName(true);
		$this->getEnvironment()->loadFromEnvFile($localEnvFile);
	}

	protected function loadEnvironmentDotEnvFile(): void
	{
		$envName = $this->getEnvironment()->environmentName();

		$envFile = $this->getEnvironment()->getDotEnvFileName(envName: $envName);
		$this->getEnvironment()->loadFromEnvFile($envFile);

		$localEnvFile = $this->getEnvironment()->getDotEnvFileName(true, $envName);
		$this->getEnvironment()->loadFromEnvFile($localEnvFile);
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
