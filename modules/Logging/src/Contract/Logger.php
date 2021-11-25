<?php
declare(strict_types=1);

namespace Elephox\Logging\Contract;

use Stringable;
use Throwable;

interface Logger
{
	public function log(string|Stringable|Throwable $message, LogLevel $level, array $metaData = []): void;

	public function emergency(string $message, array $metaData = []): void;

	public function alert(string $message, array $metaData = []): void;

	public function critical(string $message, array $metaData = []): void;

	public function error(string $message, array $metaData = []): void;

	public function warning(string $message, array $metaData = []): void;

	public function info(string $message, array $metaData = []): void;

	public function debug(string $message, array $metaData = []): void;
}
