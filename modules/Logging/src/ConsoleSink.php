<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\LogLevel as LogLevelContract;
use Elephox\Logging\Contract\Sink;
use JsonException;
use ricardoboss\Console;

class ConsoleSink implements Sink
{
	public function write(string $message, LogLevelContract $level, array $metaData): void
	{
		$method = match ($level->getLevel()) {
			LogLevel::DEBUG->getLevel() => 'debug',
			LogLevel::INFO->getLevel() => 'info',
			LogLevel::WARNING->getLevel() => 'warn',
			LogLevel::ERROR->getLevel() => 'error',
			LogLevel::CRITICAL->getLevel() => 'critical',
			LogLevel::ALERT->getLevel() => 'alert',
			LogLevel::EMERGENCY->getLevel() => 'emergency',
			default => 'notice',
		};

		if (empty($metaData)) {
			$metaDataSuffix = '';
		} else {
			try {
				$metaDataSuffix = ' ' . Console::light_gray(json_encode($metaData, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_THROW_ON_ERROR));
			} catch (JsonException $e) {
				$metaDataSuffix = ' ' . Console::light_gray("[JSON_ENCODE_ERROR: {$e->getMessage()}]");
			}
		}

		Console::$method($message . $metaDataSuffix);
	}
}
