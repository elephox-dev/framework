<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Attribute;

#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD)]
class HandlerAttribute
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
