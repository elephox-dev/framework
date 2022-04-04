<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\LogLevel;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\LogLevel as LogLevelEnum;

class FormattingConsoleSink implements Sink
{
	public function __construct(private readonly Sink $child)
	{
	}

	public function write(string $message, LogLevel $level, array $metaData): void
	{
		foreach ([
			'black' => 30,
			'red' => 31,
			'green' => 32,
			'yellow' => 33,
			'blue' => 34,
			'magenta' => 35,
			'cyan' => 36,
			'gray' => 90,
			'white' => 97,
			'default' => 39,
		] as $color => $code) {
			$closer = match ($level->getLevel()) {
				LogLevelEnum::DEBUG->getLevel() => "\033[0;90m",
				LogLevelEnum::WARNING->getLevel() => "\033[0;33m",
				LogLevelEnum::ERROR->getLevel(),
				LogLevelEnum::EMERGENCY->getLevel() => "\033[0;31m",
				LogLevelEnum::CRITICAL->getLevel() => "\033[0;35m",
				default => "\033[0m",
			};
			$message = (string)preg_replace(
				"/<$color>(.*?)<\/$color>/",
				"\033[0;{$code}m$1$closer",
				$message
			);
		}

		$this->child->write($message, $level, $metaData);
	}
}
