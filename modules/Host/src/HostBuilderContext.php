<?php
declare(strict_types=1);

namespace Elephox\Host;

use Elephox\Collection\ObjectMap;
use Elephox\Configuration\Contract\Configuration;
use Elephox\Host\Contract\HostEnvironment;

class HostBuilderContext
{
	public function __construct(
		public readonly HostEnvironment $environment,
		public readonly Configuration $configuration,
		public readonly ObjectMap $properties
	)
	{
	}
}
