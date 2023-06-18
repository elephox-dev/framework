<?php
declare(strict_types=1);

namespace Elephox\Logging\Contract;

use Elephox\Logging\SinkCapability;

interface Sink
{
	public function hasCapability(SinkCapability $capability): bool;

	public function write(LogLevel $level, string $message, ?array $context): void;
}
