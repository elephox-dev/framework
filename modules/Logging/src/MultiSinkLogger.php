<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\SinkLogger;
use Psr\Log\LoggerInterface;
use ricardoboss\Console;

class MultiSinkLogger implements LoggerInterface, SinkLogger
{
	use LogLevelProxy;
	use LogsToSink;

	/**
	 * @var list<Contract\Sink>
	 */
	private array $sinks;

	public function __construct()
	{
		$this->sinks = [];
	}

	public function addSink(Contract\Sink $sink): void
	{
		$this->sinks[] = $sink;
	}

	protected function logToSink(Contract\LogLevel $level, string $message, array $context): void
	{
		if (!$this->hasCapability(SinkCapability::AnsiFormatting)) {
			$message = Console::strip($message);
		}

		foreach ($this->sinks as $sink) {
			$sink->write($level, $message, $context);
		}
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		foreach ($this->sinks as $sink) {
			if ($sink->hasCapability($capability)) {
				return true;
			}
		}

		return false;
	}
}
