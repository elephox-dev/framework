<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\Logger;

abstract class AbstractLogger implements Logger
{
	public function emergency(string $message, array $metaData = []): void
	{
		$this->log($message, LogLevel::EMERGENCY, $metaData);
	}

	public function alert(string $message, array $metaData = []): void
	{
		$this->log($message, LogLevel::ALERT, $metaData);
	}

	public function critical(string $message, array $metaData = []): void
	{
		$this->log($message, LogLevel::CRITICAL, $metaData);
	}

	public function error(string $message, array $metaData = []): void
	{
		$this->log($message, LogLevel::ERROR, $metaData);
	}

	public function warning(string $message, array $metaData = []): void
	{
		$this->log($message, LogLevel::WARNING, $metaData);
	}

	public function info(string $message, array $metaData = []): void
	{
		$this->log($message, LogLevel::INFO, $metaData);
	}

	public function debug(string $message, array $metaData = []): void
	{
		$this->log($message, LogLevel::DEBUG, $metaData);
	}
}
