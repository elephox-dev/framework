<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\LogLevel as LogLevelContract;
use Elephox\Logging\Contract\Sink;

class StandardSink implements Sink
{
	public function write(LogLevelContract $level, string $message, array $context): void
	{
		if ($level->getLevel() > LogLevel::WARNING->getLevel()) {
			fwrite(STDERR, $message . PHP_EOL);
		} else {
			fwrite(STDOUT, $message . PHP_EOL);
		}
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		// TODO: check if current shell actually supports ANSI formatting
		return $capability === SinkCapability::AnsiFormatting;
	}
}
