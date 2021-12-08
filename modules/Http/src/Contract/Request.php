<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

interface Request extends HttpMessage, RequestInterface
{
	public function getHeaderMap(): RequestHeaderMap;

	public function getRequestMethod(): RequestMethod;

	public function withRequestMethod(RequestMethod $method): static;
}
