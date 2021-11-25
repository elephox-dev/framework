<?php
declare(strict_types=1);

namespace Elephox\Logging;

class CustomLogLevel implements Contract\LogLevel
{
	/**
	 * @param non-empty-string $name
	 * @param int $level
	 */
	public function __construct(
		private string $name,
		private int $level
	)
	{
	}

	public function getLevel(): int
	{
		return $this->level;
	}

	public function getName(): string
	{
		return $this->name;
	}
}
