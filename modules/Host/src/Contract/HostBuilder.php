<?php
declare(strict_types=1);

namespace Elephox\Host\Contract;

use Elephox\Collection\ObjectMap;
use Elephox\Configuration\Contract\ConfigurationBuilder;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Host\HostBuilderContext;

interface HostBuilder
{
	public function build(): Host;

	/**
	 * @return ObjectMap<object, object>
	 */
	public function getProperties(): ObjectMap;

	/**
	 * @param callable(ServiceCollection, HostBuilderContext): void $serviceConfigurator
	 * @return $this
	 */
	public function configureServices(callable $serviceConfigurator): static;

	/**
	 * @param callable(ConfigurationBuilder, HostBuilderContext): void $appConfigurator
	 * @return $this
	 */
	public function configureAppConfiguration(callable $appConfigurator): static;
}
