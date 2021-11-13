<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

use Elephox\DI\Contract\Container;
use Elephox\Http\Contract\Request;

class RequestContext implements Contract\RequestContext
{
	public function __construct(
		private Container $container,
		private Request $request
	)
	{
	}

	public function getActionType(): ActionType
	{
		return ActionType::Request;
	}

	public function getRequest(): Request
	{
		return $this->request;
	}

	public function getContainer(): Container
	{
		return $this->container;
	}
}
