<?php
declare(strict_types=1);

namespace Elephox\Logging\Contract;

interface Sink
{
	public function write(LogLevel $level, string $message, array $context): void;
}
