<?php
declare(strict_types=1);

namespace Elephox\Host\Contract;

use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Host\WebHostBuilderContext;

interface WebHostBuilder
{
	public function build(): WebHost;

	/**
	 * @param callable(ServiceCollection, WebHostBuilderContext): void $serviceConfigurator
	 * @return $this
	 */
	public function configureServices(callable $serviceConfigurator): static;

	/**
	 * @param callable(ConfigurationBuilder, WebHostBuilderContext): void $appConfigurator
	 * @return $this
	 */
	public function configureAppConfiguration(callable $appConfigurator): static;
}
