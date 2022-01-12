<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\RequestMethod;

interface RequestBuilder extends MessageBuilder
{
	public function requestMethod(RequestMethod $requestMethod): RequestBuilder;

	public function requestUri(Url $url): RequestBuilder;

	public function build(): Request;
}
