<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Stringable;
use Throwable;

trait LogsToSink
{
	abstract protected function logToSink(Contract\LogLevel $level, string $message, array $context): void;

	public function log(mixed $level, Stringable|string $message, array $context = []): void
	{
		if ($message instanceof Throwable) {
			$message = $message->getMessage();
			if (!array_key_exists('exception', $context)) {
				$context['exception'] = $message;
			}
		} elseif ($message instanceof Stringable) {
			$message = $message->__toString();
		}

		$this->logToSink(CustomLogLevel::fromMixed($level), $message, $context);
	}
}
