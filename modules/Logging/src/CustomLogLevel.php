<?php
declare(strict_types=1);

namespace Elephox\Logging;

use Psr\Log\InvalidArgumentException;

class CustomLogLevel implements Contract\LogLevel
{
	public static function fromMixed(mixed $level): Contract\LogLevel
	{
		if ($level instanceof Contract\LogLevel) {
			return $level;
		}

		if (!is_string($level) && !is_numeric($level)) {
			throw new InvalidArgumentException('Custom log level must be a string or integer');
		}

		if (empty($level)) {
			throw new InvalidArgumentException('Custom log level cannot be empty');
		}

		$nonEmptyLevel = (string) $level;

		if (is_numeric($level)) {
			$intLevel = (int) filter_var($level, FILTER_VALIDATE_INT);
		} else {
			$intLevel = null;
		}

		foreach (LogLevel::cases() as $enum) {
			if ($enum->value === $intLevel || strtolower($enum->name) === strtolower($nonEmptyLevel)) {
				return $enum;
			}
		}

		return new CustomLogLevel($nonEmptyLevel, $intLevel ?? 0);
	}

	/**
	 * @param non-empty-string $name
	 * @param int $level
	 */
	public function __construct(
		private readonly string $name,
		private readonly int $level,
	) {
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
