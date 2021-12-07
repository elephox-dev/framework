<?php
declare(strict_types=1);

namespace Elephox\Core\Context;

use Elephox\Core\Handler\ActionType;
use Elephox\DI\Contract\Container;
use Elephox\Http\Contract\Request;
use Psr\Http\Message\RequestInterface;

class RequestContext extends AbstractContext implements Contract\RequestContext
{
	public function __construct(
		Container $container,
		private RequestInterface $request
	)
	{
		parent::__construct(ActionType::Request, $container);

		$container->register(Contract\RequestContext::class, $this);
	}

	public function getRequest(): RequestInterface
	{
		return $this->request;
	}
}
