<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\Sink;
use Elephox\Logging\Contract\SinkLogger;
use Psr\Log\LoggerInterface;
use ricardoboss\Console;

class SingleSinkLogger implements LoggerInterface, SinkLogger
{
	use LogLevelProxy;
	use LogsToSink;

	public function __construct(
		private readonly Sink $sink,
	) {
	}

	protected function logToSink(Contract\LogLevel $level, string $message, array $context): void
	{
		if (!$this->hasCapability(SinkCapability::AnsiFormatting)) {
			$message = Console::strip($message);
		}

		$this->sink->write($level, $message, $context);
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		return $this->sink->hasCapability($capability);
	}
}
