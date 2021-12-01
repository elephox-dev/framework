<?php
declare(strict_types=1);

namespace Elephox\Logging\Contract;

interface Sink
{
	public function write(string $message, LogLevel $level, array $metaData): void;
}
