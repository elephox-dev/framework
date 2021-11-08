<?php
declare(strict_types=1);

namespace Philly\Http;

class Request implements Contract\Request
{
	private string $uri;

	private RequestMethod $method;

	private Contract\ReadonlyHeaderMap $headers;

	public function __construct(string $uri, RequestMethod $method, Contract\ReadonlyHeaderMap|array $headers)
	{
		$this->uri = $uri;
		$this->method = $method;

		/** @var Contract\ReadonlyHeaderMap headers */
		$this->headers = $headers instanceof Contract\ReadonlyHeaderMap ?
			$headers :
			HeaderMap::fromArray($headers);
	}

	public function getUri(): string
	{
		return $this->uri;
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
