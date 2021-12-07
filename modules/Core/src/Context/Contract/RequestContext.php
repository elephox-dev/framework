<?php
declare(strict_types=1);

namespace Elephox\Core\Context\Contract;

use Psr\Http\Message\RequestInterface;

interface RequestContext extends Context
{
	public function getRequest(): RequestInterface;
}
