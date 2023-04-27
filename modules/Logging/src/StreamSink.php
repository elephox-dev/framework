<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\LogLevel as LogLevelContract;
use Elephox\Logging\Contract\Sink;
use Elephox\Stream\Contract\Stream;
use InvalidArgumentException;

readonly class StreamSink implements Sink
{
	private Stream $errorStream;

	public function __construct(
		private Stream $stream,
		?Stream $errorStream = null,
		private string $eol = PHP_EOL,
	) {
		if (!$stream->isWritable()) {
			throw new InvalidArgumentException('Given stream is not writable');
		}

		if ($errorStream !== null && !$errorStream->isWritable()) {
			throw new InvalidArgumentException('Given error stream is not writable');
		}

		$this->errorStream = $errorStream ?? $stream;
	}

	public function write(LogLevelContract $level, string $message, array $context): void
	{
		if ($level->getLevel() >= LogLevel::WARNING->getLevel()) {
			$this->errorStream->write($message . $this->eol);
		} else {
			$this->stream->write($message . $this->eol);
		}
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		return false;
	}
}
