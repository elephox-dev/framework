<?php
declare(strict_types=1);

namespace Elephox\Core;

enum ActionType implements Contract\ActionType
{
	case Any;
	case Command;
	case Event;
	case Request;
	case Exception;

	public function getName(): string
	{
		return $this->name;
	}

	public function matchesAny(): bool
	{
		return $this === self::Any;
	}
}
