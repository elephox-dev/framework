<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Configuration\Contract\ConfigurationRoot;
use Elephox\DI\Contract\ServiceCollection;
use Elephox\Host\Contract\HostEnvironment;

class ConsoleHost
{
	public function __construct(
		public readonly ServiceCollection $services,
		public readonly HostEnvironment $environment,
		public readonly ConfigurationRoot $configuration,
	)
	{
	}
}
