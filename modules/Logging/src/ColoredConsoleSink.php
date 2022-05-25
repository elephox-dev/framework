<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Elephox\Logging\Contract\LogLevel as LogLevelContract;
use Elephox\Logging\Contract\Sink;
use JsonException;
use ricardoboss\Console;

class ColoredConsoleSink implements Sink
{
	public function write(LogLevelContract $level, string $message, array $context): void
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

		if (empty($context)) {
			$metaDataSuffix = '';
		} else {
			try {
				$metaDataSuffix = ' ' . Console::light_gray((string) json_encode($context, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK | JSON_THROW_ON_ERROR));
			} catch (JsonException $e) {
				$metaDataSuffix = ' ' . Console::light_gray("[JSON_ENCODE_ERROR: {$e->getMessage()}]");
			}
		}

		Console::$method($message . $metaDataSuffix);
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		return $capability === SinkCapability::AnsiFormatting;
	}
}
