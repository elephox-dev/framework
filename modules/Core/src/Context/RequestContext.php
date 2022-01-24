<?php
declare(strict_types=1);

namespace Elephox\Core\Context;

use Elephox\Core\ActionType;
use Elephox\Http\Contract\Request;
use Elephox\DI\Contract\Container;

class RequestContext extends AbstractContext implements Contract\RequestContext
{
	public function __construct(
		Container $container,
		private Request $request
	) {
		parent::__construct(ActionType::Request, $container);

		$container->register(Contract\RequestContext::class, $this);
	}

	public function getRequest(): Request
	{
		return $this->request;
	}
}
