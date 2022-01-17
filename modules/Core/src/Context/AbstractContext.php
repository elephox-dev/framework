<?php
declare(strict_types=1);

namespace Elephox\Core\Context;

use Elephox\Core\ActionType;
use Elephox\DI\Contract\Container;

abstract class AbstractContext implements Contract\Context
{
	protected function __construct(
		private ActionType $actionType,
		private Container $container,
	)
	{
		$this->container->register(Contract\Context::class, $this);
		$this->container->alias(self::class, Contract\Context::class);
		$this->container->alias(static::class, Contract\Context::class);
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
