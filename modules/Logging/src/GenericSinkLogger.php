<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\LogLevel;
use Elephox\Support\Contract\StringConvertible;
use Stringable;
use Throwable;

class GenericSinkLogger extends AbstractLogger
{
	public function __construct(
		private Contract\Sink $sink,
	)
	{
	}

	public function log(Throwable|StringConvertible|Stringable|string $message, LogLevel $level, array $metaData = []): void
	{
		if ($message instanceof Throwable) {
			$message = $message->getMessage();
		} else if ($message instanceof StringConvertible) {
			$message = $message->__toString();
		} else if ($message instanceof Stringable) {
			$message = $message->__toString();
		}

		$this->sink->write($message, $level, $metaData);
	}
}
