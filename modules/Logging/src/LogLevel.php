<?php
declare(strict_types=1);

namespace Elephox\Logging;

enum LogLevel: int implements Contract\LogLevel
{
	case DEBUG = 0;
	case INFO = 1;
	case WARNING = 2;
	case ERROR = 3;
	case CRITICAL = 4;
	case ALERT = 5;
	case EMERGENCY = 6;

	public function getLevel(): int
	{
		return $this->value;
	}

	public function getName(): string
	{
		/**
		 * This annotation can be removed when vimeo/psalm#6964 is resolved.
		 *
		 * @var non-empty-string
		 */
		return $this->name;
	}
}
