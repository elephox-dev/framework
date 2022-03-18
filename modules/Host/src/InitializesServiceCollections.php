<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\DI\Container;
use Elephox\DI\Contract\Container as ContainerContract;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\DI\ServiceCollection;

trait InitializesServiceCollections
{
	protected function initializeServiceCollection(ServiceCollectionContract $collection): void
	{
		$container = $this->getInitialContainer();

		$collection->addSingleton(
			ContainerContract::class,
			Container::class,
			implementation: $container
		);

		$collection->setAlias('container', ContainerContract::class);

		$collection->addSingleton(
			ServiceCollectionContract::class,
			ServiceCollection::class,
			implementation: $collection
		);

		$collection->setAlias('services', ServiceCollectionContract::class);
	}

	protected function getInitialContainer(): ContainerContract
	{
		return new Container();
	}
}
