<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\LogLevel;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\Contract\SinkProxy;
use Elephox\Logging\LogLevel as LogLevelEnum;

class SimpleFormatColorSink implements Sink, SinkProxy
{
	public function __construct(private readonly Sink $innerSink)
	{
	}

	public function write(LogLevel $level, string $message, array $context): void
	{
		if (!$this->getInnerSink()->hasCapability(SinkCapability::AnsiFormatting)) {
			// TODO: remove formatting tags from message
			$this->getInnerSink()->write($level, $message, $context);

			return;
		}

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
			$opener = "\033[{$code}m";
			$closer = match ($level->getLevel()) {
				LogLevelEnum::DEBUG->getLevel() => "\033[90m",
				LogLevelEnum::WARNING->getLevel() => "\033[33m",
				LogLevelEnum::ERROR->getLevel() => "\033[31m",
				LogLevelEnum::CRITICAL->getLevel() => "\033[35m",
				default => "\033[39m",
			};
			$message = (string) preg_replace(
				"/<$color>(.*?)<\/$color>/",
				"$opener$1$closer",
				$message,
			);
		}

		foreach ([
			'blackBack' => 40,
			'redBack' => 41,
			'greenBack' => 42,
			'yellowBack' => 43,
			'blueBack' => 44,
			'magentaBack' => 45,
			'cyanBack' => 46,
			'grayBack' => 100,
			'whiteBack' => 107,
			'defaultBack' => 49,
		] as $color => $code) {
			$opener = "\033[{$code}m";
			$closer = match ($level->getLevel()) {
				LogLevelEnum::DEBUG->getLevel() => "\033[90;49m",
				LogLevelEnum::WARNING->getLevel() => "\033[33;49m",
				LogLevelEnum::ERROR->getLevel() => "\033[31;49m",
				LogLevelEnum::CRITICAL->getLevel() => "\033[35;49m",
				LogLevelEnum::ALERT->getLevel() => "\033[97;43m",
				LogLevelEnum::EMERGENCY->getLevel() => "\033[97;41m",
				default => "\033[49m",
			};
			$message = (string) preg_replace(
				"/<$color>(.*?)<\/$color>/",
				"$opener$1$closer",
				$message,
			);
		}

		foreach ([
			'bold' => [1, 22],
			'underline' => [4, 24],
			'blink' => [5, 25],
			'inverse' => [7, 27],
			'hidden' => [8, 28],
		] as $option => $codes) {
			$opener = "\033[$codes[0]m";
			$closer = "\033[$codes[1]m";
			$message = (string) preg_replace(
				"/<$option>(.*?)<\/$option>/",
				"$opener$1$closer",
				$message,
			);
		}

		$this->getInnerSink()->write($level, $message, $context);
	}

	public function getInnerSink(): Sink
	{
		return $this->innerSink;
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		return $capability === SinkCapability::ElephoxFormatting || $this->getInnerSink()->hasCapability($capability);
	}
}
