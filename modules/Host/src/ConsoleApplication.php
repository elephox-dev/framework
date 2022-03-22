<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Core\Context\CommandLineContext;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\DI\ServiceCollection;
use Elephox\Host\Contract\HostEnvironment;

class ConsoleApplication
{
	public function __construct(
		public readonly ServiceCollectionContract $services,
		public readonly HostEnvironment $environment,
		public readonly ConfigurationRoot $configuration,
	)
	{
	}

	public static function createBuilder(): ConsoleApplicationBuilder
	{
		$configuration = new ConfigurationManager();
		$environment = new GlobalHostEnvironment();
		$services = new ServiceCollection();

		return new ConsoleApplicationBuilder(
			$configuration,
			$environment,
			$services,
		);
	}

	public function handle(): void
	{
	}
}
