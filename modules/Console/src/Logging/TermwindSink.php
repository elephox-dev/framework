<?php
declare(strict_types=1);

namespace Elephox\Console\Logging;

use DateTime;
use Elephox\Logging\Contract\LogLevel;
use Elephox\Logging\Contract\Sink;
use Elephox\Logging\SinkCapability;
use function Termwind\render;
use function Termwind\style;

/**
 * @psalm-suppress InternalMethod
 */
class TermwindSink implements Sink
{
	public function __construct()
	{
		$this->setEmergencyStyle();
		$this->setAlertStyle();
		$this->setCriticalStyle();
		$this->setErrorStyle();
		$this->setWarningStyle();
		$this->setInfoStyle();
		$this->setNoticeStyle();
		$this->setDebugStyle();
	}

	protected function setEmergencyStyle(): void
	{
		style('level-emergency')->apply('font-bold text-white bg-red p-4 m-1');
	}

	protected function setAlertStyle(): void
	{
		style('level-alert')->apply('font-bold text-black bg-yellow p-4');
	}

	protected function setCriticalStyle(): void
	{
		style('level-critical')->apply('font-normal text-magenta font-bold');
	}

	protected function setErrorStyle(): void
	{
		style('level-error')->apply('font-normal text-red');
	}

	protected function setWarningStyle(): void
	{
		style('level-warning')->apply('font-normal text-yellow');
	}

	protected function setNoticeStyle(): void
	{
		style('level-notice')->apply('font-normal text-cyan');
	}

	protected function setInfoStyle(): void
	{
		style('level-info')->apply('font-normal text-white');
	}

	protected function setDebugStyle(): void
	{
		style('level-debug')->apply('font-normal text-gray');
	}

	protected function getTimestampFormat(): string
	{
		return 'd.m.y H:i:s.v';
	}

	protected function getCurrentTimestamp(): string
	{
		return (new DateTime())->format($this->getTimestampFormat());
	}

	protected function formatTimestamp(): string
	{
		$timestamp = $this->getCurrentTimestamp();

		return "<span>[$timestamp]</span>";
	}

	public function write(LogLevel $level, string $message, array $context): void
	{
		$className = strtolower($level->getName());
		$timestamp = $this->formatTimestamp();

		render(
			<<<HTML
<div class="level-$className">
	$timestamp
	$message
</div>
HTML,
		);
	}

	public function hasCapability(SinkCapability $capability): bool
	{
		return $capability === SinkCapability::SymfonyFormatting;
	}
}
