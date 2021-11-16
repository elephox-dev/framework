<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

abstract class HandlerAttribute
{
	public function __construct(
		private ActionType $type,
	)
	{
	}

	public function getType(): ActionType
	{
		return $this->type;
	}
}
