<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\Contract\GenericList;
use Elephox\Collection\Contract\GenericMap;
use Elephox\Http\Contract\Cookie;

class ServerRequest extends Request implements Contract\ServerRequest
{
	public function getServerParams(): array
	{
		// TODO: Implement getServerParams() method.
	}

	public function getServerParamsMap(): GenericMap
	{
		// TODO: Implement getServerParamsMap() method.
	}

	public function getCookieParams(): array
	{
		// TODO: Implement getCookieParams() method.
	}

	public function getCookies(): GenericList
	{
		// TODO: Implement getCookies() method.
	}

	public function withCookieParams(array $cookies): static
	{
		// TODO: Implement withCookieParams() method.
	}

	public function withCookies(iterable $cookies): static
	{
		// TODO: Implement withCookies() method.
	}

	public function withCookie(Cookie $cookie): static
	{
		// TODO: Implement withCookie() method.
	}

	public function getQueryParams(): array
	{
		// TODO: Implement getQueryParams() method.
	}

	public function withQueryParams(array $query): static
	{
		// TODO: Implement withQueryParams() method.
	}

	public function getUploadedFiles(): array
	{
		// TODO: Implement getUploadedFiles() method.
	}

	public function withUploadedFiles(array $uploadedFiles): static
	{
		// TODO: Implement withUploadedFiles() method.
	}

	public function getParsedBody(): null|array|object
	{
		// TODO: Implement getParsedBody() method.
	}

	public function withParsedBody($data): static
	{
		// TODO: Implement withParsedBody() method.
	}

	public function getAttributes(): array
	{
		// TODO: Implement getAttributes() method.
	}

	public function getAttribute($name, $default = null): mixed
	{
		// TODO: Implement getAttribute() method.
	}

	public function withAttribute($name, $value): static
	{
		// TODO: Implement withAttribute() method.
	}

	public function withoutAttribute($name): static
	{
		// TODO: Implement withoutAttribute() method.
	}
}
