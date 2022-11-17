<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\LogLevel as LogLevelContract;
use Elephox\Logging\Contract\Sink;
use Elephox\Stream\Contract\Stream;
use InvalidArgumentException;

class StreamSink implements Sink
{
	private readonly Stream $stream;
	private readonly Stream $errorStream;

	public function __construct(
		Stream $stream,
		?Stream $errorStream = null,
	) {
		if (!$stream->isWritable()) {
			throw new InvalidArgumentException('Given stream is not writable');
		}

		if ($errorStream !== null && !$errorStream->isWritable()) {
			throw new InvalidArgumentException('Given error stream is not writable');
		}

		$this->stream = $stream;
		$this->errorStream = $errorStream ?? $stream;
	}

	public function write(LogLevelContract $level, string $message, array $context): void
	{
		if ($level->getLevel() >= LogLevel::WARNING->getLevel()) {
			$this->errorStream->write($message . PHP_EOL);
		} else {
			$this->stream->write($message . PHP_EOL);
		}
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		return false;
	}
}
