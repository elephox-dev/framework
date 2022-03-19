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

	public function getRequestMethod(): ?RequestMethod;

	public function requestUrl(Url $url): static;

	public function getRequestUrl(): ?Url;

	public function get(): Request;
}
