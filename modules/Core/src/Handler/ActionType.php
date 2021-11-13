<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

enum ActionType
{
	case Command;
	case Event;
	case Request;
}
