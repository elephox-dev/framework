<?php
declare(strict_types=1);

namespace Elephox\Core;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
enum ActionType implements Contract\ActionType
{
	case Any;
	case Command;
	case Event;
	case Request;
	case Exception;

	#[Pure]
	public function getName(): string
	{
		return $this->name;
	}

	#[Pure]
	public function matchesAny(): bool
	{
		return $this === self::Any;
	}
}
