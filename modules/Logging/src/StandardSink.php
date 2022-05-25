<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\LogLevel;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\LogLevel as ElephoxLogLevel;

class StandardSink implements Sink
{
	public function write(LogLevel $level, string $message, array $context): void
	{
		if ($level->getLevel() > ElephoxLogLevel::WARNING->getLevel()) {
			fwrite(STDERR, $message);
		} else {
			fwrite(STDOUT, $message);
		}
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		return $capability === SinkCapability::AnsiFormatting;
	}
}
