<?php
declare(strict_types=1);

namespace Elephox\Core\Context;

use Elephox\Core\Handler\ActionType;
use Elephox\DI\Contract\Container;

abstract class AbstractContext implements Contract\Context
{
	protected function __construct(
		private ActionType $actionType,
		private Container $container,
	)
	{
		$this->container->register(Contract\Context::class, $this);
		$this->container->register(self::class, $this);
	}

	final public function getActionType(): ActionType
	{
		return $this->actionType;
	}

	public function getContainer(): Container
	{
		return $this->container;
	}
}
