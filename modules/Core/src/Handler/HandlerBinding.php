<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\Core\Handler\Contract\Context;

class HandlerBinding implements Contract\HandlerBinding
{
	public function __construct(
		private object $handler,
		private string $method,
		private ActionType $actionType,
	)
	{
	}

	public function getMethodName(): string
	{
		return $this->method;
	}

	public function getHandler(): object
	{
		return $this->handler;
	}

	public function isApplicable(Context $context): bool
	{
		return $context->getActionType() === $this->actionType;
	}
}
