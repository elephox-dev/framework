<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\Sink;
use Psr\Log\LoggerInterface;

class SingleSinkLogger implements LoggerInterface
{
	use LogLevelProxy;
	use LogsToSink;

	public function __construct(
		private readonly Sink $sink,
	) {
	}

	protected function logToSink(Contract\LogLevel $level, string $message, array $context): void
	{
		$this->sink->write($level, $message, $context);
	}
}
