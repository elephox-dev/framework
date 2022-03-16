<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Configuration\Contract\Configuration;
use Elephox\Host\Contract\WebHostEnvironment;

class WebHostBuilderContext
{
	public function __construct(
		public readonly WebHostEnvironment $environment,
		public readonly Configuration $configuration,
	)
	{
	}
}
