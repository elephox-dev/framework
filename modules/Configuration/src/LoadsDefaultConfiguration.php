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
		$environment = $this->getEnvironment();

		assert($environment instanceof DotEnvEnvironment);

		$envFile = $environment->getDotEnvFileName();
		$environment->loadFromEnvFile($envFile);

		$localEnvFile = $environment->getDotEnvFileName(true);
		$environment->loadFromEnvFile($localEnvFile);
	}

	protected function loadEnvironmentDotEnvFile(): void
	{
		$environment = $this->getEnvironment();
		$envName = $environment->environmentName();

		assert($environment instanceof DotEnvEnvironment);

		$envFile = $environment->getDotEnvFileName(envName: $envName);
		$environment->loadFromEnvFile($envFile);

		$localEnvFile = $environment->getDotEnvFileName(true, $envName);
		$environment->loadFromEnvFile($localEnvFile);
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
