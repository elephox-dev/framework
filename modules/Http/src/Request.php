<?php
declare(strict_types=1);

namespace Elephox\Http;

class Request implements Contract\Request
{
	public static function fromGlobals(): Contract\Request
	{
		$headers = [];
		foreach ($_SERVER as $name => $value) {
			if (str_starts_with($name, 'HTTP_')) {
				$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}

		return new static($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"], $headers);
	}

	private Contract\Url $url;

	private Contract\RequestMethod $method;

	private Contract\ReadonlyHeaderMap $headers;

	/**
	 * @param Contract\RequestMethod|non-empty-string $method
	 */
	public function __construct(Contract\RequestMethod|string $method, Contract\Url|string $uri, Contract\ReadonlyHeaderMap|array $headers = [], private ?string $body = null, private bool $followRedirects = true)
	{
		$this->url = $uri instanceof Contract\Url ?
			$uri :
			Url::fromString($uri);

		if ($method instanceof Contract\RequestMethod) {
			$this->method = $method;
		} else {
			/**
			 * @var Contract\RequestMethod|null $parsedMethod
			 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
			 */
			$parsedMethod = RequestMethod::tryFrom($method);
			if ($parsedMethod === null) {
				$parsedMethod = new CustomRequestMethod($method);
			}

			$this->method = $parsedMethod;
		}

		/** @var Contract\ReadonlyHeaderMap headers */
		$this->headers = $headers instanceof Contract\ReadonlyHeaderMap ?
			$headers :
			RequestHeaderMap::fromArray($headers);
	}

	public function getUrl(): Contract\Url
	{
		return $this->url;
	}

	public function getMethod(): Contract\RequestMethod
	{
		return $this->method;
	}

	public function getHeaders(): Contract\ReadonlyHeaderMap
	{
		return $this->headers;
	}

	public function shouldFollowRedirects(): bool
	{
		return $this->followRedirects;
	}

	public function getBody(): ?string
	{
		return $this->body;
	}
}
