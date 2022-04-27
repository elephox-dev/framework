<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Stringable;

trait LogLevelProxy
{
	abstract public function log(mixed $level, string|Stringable $message, array $context = []): void;

	public function emergency(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::EMERGENCY, $message, $context);
	}

	public function alert(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::ALERT, $message, $context);
	}

	public function critical(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::CRITICAL, $message, $context);
	}

	public function error(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::ERROR, $message, $context);
	}

	public function warning(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::WARNING, $message, $context);
	}

	public function notice(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::NOTICE, $message, $context);
	}

	public function info(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::INFO, $message, $context);
	}

	public function debug(string|Stringable $message, array $context = []): void
	{
		$this->log(LogLevel::DEBUG, $message, $context);
	}
}
