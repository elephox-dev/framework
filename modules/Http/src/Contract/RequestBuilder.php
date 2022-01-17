<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\RequestMethod;
use Elephox\Http\Url;

interface RequestBuilder extends MessageBuilder
{
	public function requestMethod(RequestMethod $requestMethod): RequestBuilder;

	public function requestUrl(Url $url): RequestBuilder;

	public function get(): Request;
}
