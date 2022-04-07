<?php
declare(strict_types=1);

namespace Elephox\Development;

use Elephox\Configuration\Json\JsonFileConfigurationSource;
use Elephox\Console\ConsoleApplicationBuilder;

class DevConsoleApplicationBuilder extends ConsoleApplicationBuilder
{
	protected function registerDefaultConfig(): void
	{
		// Dev app is always in development mode
		$this->configuration->offsetSet('env:name', 'development');
	}
}
