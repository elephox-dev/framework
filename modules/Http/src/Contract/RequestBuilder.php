<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Http\RequestMethod;
use Elephox\Http\Url;

/**
 * @psalm-consistent-constructor
 */
interface RequestBuilder extends MessageBuilder
{
	public function requestMethod(RequestMethod $requestMethod): static;

	public function requestUrl(Url $url): static;

	public function get(): Request;
}
