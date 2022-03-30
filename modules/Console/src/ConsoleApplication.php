<?php
declare(strict_types=1);

namespace Elephox\Console;

use Elephox\Configuration\ConfigurationManager;
use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\Console\Contract\ConsoleEnvironment;
use Elephox\DI\Contract\ServiceCollection as ServiceCollectionContract;
use Elephox\DI\ServiceCollection;

class ConsoleApplication
{
	public function __construct(
		public readonly ServiceCollectionContract $services,
		public readonly ConsoleEnvironment $environment,
		public readonly ConfigurationRoot $configuration,
	)
	{
	}

	public static function createBuilder(): ConsoleApplicationBuilder
	{
		$configuration = new ConfigurationManager();
		$environment = new GlobalConsoleEnvironment();
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
