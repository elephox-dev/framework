<?php
declare(strict_types=1);

namespace Philly\Http;

class Request implements Contract\Request
{
	private Contract\Url $url;

	private RequestMethod $method;

	private Contract\ReadonlyHeaderMap $headers;

	public function __construct(RequestMethod|string $method, Contract\Url|string $uri, Contract\ReadonlyHeaderMap|array $headers = [])
	{
		$this->url = is_string($uri) ?
			Url::fromString($uri) :
			$uri;

		/**
		 * @var RequestMethod method
		 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
		 */
		$this->method = is_string($method) ?
			RequestMethod::from($method) :
			$method;

		/** @var Contract\ReadonlyHeaderMap headers */
		$this->headers = $headers instanceof Contract\ReadonlyHeaderMap ?
			$headers :
			HeaderMap::fromArray($headers);
	}

	public function getUrl(): Contract\Url
	{
		return $this->url;
	}

	public function getMethod(): RequestMethod
	{
		return $this->method;
	}

	public function getHeaders(): Contract\ReadonlyHeaderMap
	{
		return $this->headers;
	}
}
