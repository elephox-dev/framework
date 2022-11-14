<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\LogLevel as LogLevelContract;
use Elephox\Logging\Contract\Sink;
use const STDERR;
use const STDOUT;

class StandardSink implements Sink
{
	public function __construct()
	{
		assert(defined("STDOUT"), sprintf("Cannot use %s because STDOUT is not defined", __CLASS__));
		assert(defined("STDERR"), sprintf("Cannot use %s because STDERR is not defined", __CLASS__));
	}

	public function write(LogLevelContract $level, string $message, array $context): void
	{
		if ($level->getLevel() >= LogLevel::WARNING->getLevel()) {
			fwrite(STDERR, $message . PHP_EOL);
		} else {
			fwrite(STDOUT, $message . PHP_EOL);
		}
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		return stream_isatty(STDOUT) && stream_isatty(STDERR) && $capability === SinkCapability::AnsiFormatting;
	}
}
