<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Core\Context\Contract\Context;
use Elephox\Core\Contract\ActionType;
use Elephox\Core\Handler\Contract\HandlerMeta;

abstract class AbstractHandlerMeta implements HandlerMeta
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

	public function handles(Context $context): bool
	{
		return $this->getType()->matchesAny() || $this->getType()->getName() === $context->getActionType()->getName();
	}

	public function getWeight(): int
	{
		return $this->weight;
	}

	public function getHandlerParams(Context $context): iterable
	{
		return [];
	}
}
