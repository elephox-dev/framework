<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Psr\Log\LoggerInterface;

class MultiSinkLogger implements LoggerInterface
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
		foreach ($this->sinks as $sink) {
			$sink->write($level, $message, $context);
		}
	}
}
