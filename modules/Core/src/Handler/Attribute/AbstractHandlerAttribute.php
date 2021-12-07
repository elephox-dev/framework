<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Attribute;

use Closure;
use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Handler\Attribute\Contract\HandlerAttribute;
use Elephox\Core\Handler\Contract\ActionType;

abstract class AbstractHandlerAttribute implements HandlerAttribute
{
	public function __construct(
		private ActionType $type,
		private int $weight,
	)
	{
	}

	final public function getType(): ActionType
	{
		return $this->type;
	}

	public function getWeight(): int
	{
		return $this->weight;
	}
}
