<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Configuration\Contract\Configuration;
use Elephox\Host\Contract\HostEnvironment;

class ConsoleHostBuilderContext
{
	public function __construct(
		public readonly HostEnvironment $environment,
		public readonly Configuration $configuration,
	)
	{
	}
}
