<?php
declare(strict_types=1);

namespace Elephox\Logging;

use DateTime;
use Elephox\Logging\Contract\LogLevel as LogLevelContract;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\Contract\SinkProxy;

class EnhancedMessageSink implements Sink, SinkProxy
{
	private readonly bool $useFormatting;

	public function __construct(
		private readonly Sink $innerSink,
	) {
		$this->useFormatting = $this->getInnerSink()->hasCapability(SinkCapability::ElephoxFormatting);
	}

	public function write(LogLevelContract $level, string $message, array $context): void
	{
		$message = $this->enhanceMessage($level, $message);

		$this->getInnerSink()->write($level, $message, $context);
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		return $this->getInnerSink()->hasCapability($capability);
	}

	public function getInnerSink(): Sink
	{
		return $this->innerSink;
	}

	protected function getTimestampFormat(): string
	{
		return 'd.m.y H:i:s.v';
	}

	protected function getCurrentTimestamp(): string
	{
		return (new DateTime())->format($this->getTimestampFormat());
	}

	protected function getDefaultFormat(): string
	{
		return '[%s] [%s] %s';
	}

	protected function getLevelName(LogLevelContract $level): string
	{
		return substr(str_pad($level->getName(), 6), 0, 6);
	}

	protected function getForeground(LogLevelContract $level): string
	{
		return match ($level->getLevel()) {
			LogLevel::DEBUG->getLevel() => 'gray',
			LogLevel::INFO->getLevel() => 'white',
			LogLevel::NOTICE->getLevel() => 'cyan',
			LogLevel::WARNING->getLevel() => 'yellow',
			LogLevel::ERROR->getLevel() => 'red',
			LogLevel::CRITICAL->getLevel() => 'magenta',
			LogLevel::ALERT->getLevel() => 'black',
			default => 'default',
		};
	}

	protected function getBackground(LogLevelContract $level): string
	{
		return match ($level->getLevel()) {
			LogLevel::ALERT->getLevel() => 'yellowBack',
			LogLevel::EMERGENCY->getLevel() => 'redBack',
			default => 'defaultBack',
		};
	}

	protected function getOptions(LogLevelContract $level): ?string
	{
		return match ($level->getLevel()) {
			LogLevel::CRITICAL->getLevel(),
			LogLevel::EMERGENCY->getLevel() => 'bold',
			default => null,
		};
	}

	protected function getEnhancedFormat(LogLevelContract $level): string
	{
		$fg = $this->getForeground($level);
		$bg = $this->getBackground($level);
		$message = "<$bg><$fg>%s</$fg></$bg>";

		$op = $this->getOptions($level);
		if ($op !== null) {
			$message = "<$op>$message</$op>";
		}

		return "<gray>[</gray>%s<gray>] [</gray>%s<gray>]</gray> $message";
	}

	protected function enhanceMessage(LogLevelContract $level, string $message): string
	{
		$format = $this->getDefaultFormat();
		$timestamp = $this->getCurrentTimestamp();
		$levelName = $this->getLevelName($level);

		if ($this->useFormatting) {
			$format = $this->getEnhancedFormat($level);
		}

		return sprintf(
			$format,
			$timestamp,
			$levelName,
			$message,
		);
	}
}
