<?php
declare(strict_types=1);

namespace Elephox\Logging;

enum LogLevel: int implements Contract\LogLevel
{
	case DEBUG = 0;
	case INFO = 1;
	case NOTICE = 2;
	case WARNING = 3;
	case ERROR = 4;
	case CRITICAL = 5;
	case ALERT = 6;
	case EMERGENCY = 7;
	public function getLevel(): int
	{
		return $this->value;
	}

	public function getName(): string
	{
		return $this->name;
	}
}
