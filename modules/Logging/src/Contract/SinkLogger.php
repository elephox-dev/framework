<?php
declare(strict_types=1);

namespace Elephox\Logging\Contract;

use Elephox\Logging\SinkCapability;

interface SinkLogger
{
	public function hasCapability(SinkCapability $capability): bool;
}
