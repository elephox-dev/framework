<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Configuration\Contract\Configuration;
use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Host\Contract\HostEnvironment;

class ConsoleHostBuilder
{
	public readonly ConsoleHostBuilderContext $context;

	public function __construct(
		public readonly HostEnvironment $environment,
		public readonly Configuration $builderConfiguration,
		public readonly ConfigurationBuilder $hostConfiguration,
		public readonly ServiceCollection $services,
	)
	{
		$this->context = new ConsoleHostBuilderContext($this->environment, $this->builderConfiguration);
	}

	public function build(): ConsoleHost
	{
		return new ConsoleHost(
			$this->services,
			$this->environment,
			$this->hostConfiguration->build()
		);
	}

	/**
	 * @param callable(ServiceCollection, ConsoleHostBuilderContext): void $serviceConfigurator
	 * @return static
	 */
	public function configureServices(callable $serviceConfigurator): static
	{
		$serviceConfigurator($this->services, $this->context);

		return $this;
	}

	/**
	 * @param callable(ConfigurationBuilder, ConsoleHostBuilderContext): void $appConfigurator
	 * @return static
	 */
	public function configureAppConfiguration(callable $appConfigurator): static
	{
		$appConfigurator($this->hostConfiguration, $this->context);

		return $this;
	}
}
