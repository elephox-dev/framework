<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

enum ActionType implements Contract\ActionType
{
	case Command;
	case Event;
	case Request;
	case Exception;

	public function getName(): string
	{
		/**
		 * @var non-empty-string value
		 * @psalm-suppress UndefinedThisPropertyFetch Until vimeo/psalm#6468 is fixed
		 */
		return $this->name;
	}
}
