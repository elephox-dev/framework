<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\OffsetNotFoundException;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

class Request implements Contract\Request
{
	public static function fromGlobals(): Contract\Request
	{
		/**
		 * @var array<string, mixed> $headers
		 */
		$headers = [];

		/**
		 * @var string $name
		 * @var mixed $value
		 */
		foreach ($_SERVER as $name => $value) {
			if (!str_starts_with($name, 'HTTP_')) {
				continue;
			}

			$normalizedName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));

			/** @var mixed */
			$headers[$normalizedName] = $value;
		}

		$headerMap = RequestHeaderMap::fromArray($headers);

		if (!array_key_exists("REQUEST_METHOD", $_SERVER) || empty($_SERVER["REQUEST_METHOD"])) {
			throw new RuntimeException("REQUEST_METHOD is not set.");
		}

		/** @var non-empty-string $method */
		$method = $_SERVER["REQUEST_METHOD"];

		/** @var string $uri */
		$uri = $_SERVER["REQUEST_URI"];

		try {
			$contentLength = (int)$headerMap->get(HeaderName::ContentLength);
		} catch (OffsetNotFoundException) {
			$contentLength = -1;
		}

		$body = file_get_contents("php://input", length: $contentLength);

		return new self($method, $uri, $headerMap, $body);
	}

	private Contract\Url $url;

	private Contract\RequestMethod $method;

	private Contract\ReadonlyHeaderMap $headers;

	/**
	 * @param Contract\RequestMethod|non-empty-string $method
	 */
	public function __construct(Contract\RequestMethod|string $method, Contract\Url|string $uri, Contract\ReadonlyHeaderMap|array $headers = [], private ?string $body = null, private bool $followRedirects = true)
	{
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

		if ($body !== null && !$this->method->canHaveBody()) {
			throw new InvalidArgumentException("Request method {$this->method->getValue()} cannot have a body.");
		}

		$this->url = $uri instanceof Contract\Url ?
			$uri :
			Url::fromString($uri);

		/** @var Contract\ReadonlyHeaderMap headers */
		$this->headers = $headers instanceof Contract\ReadonlyHeaderMap ?
			$headers :
			RequestHeaderMap::fromArray($headers);

		if ($this->headers->anyKey(static fn(Contract\HeaderName $name) => $name->isOnlyResponse())) {
			throw new InvalidArgumentException("Requests cannot contain headers reserved for responses only.");
		}
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

	public function getJson(): array
	{
		if ($this->headers->has(HeaderName::ContentType)) {
			$contentType = $this->headers->get(HeaderName::ContentType);
			if (!str_starts_with($contentType, "application/json")) {
				throw new LogicException("Content-Type is not application/json");
			}
		}

		if ($this->body === null) {
			return [];
		}

		/** @var array */
		return json_decode($this->body, true, flags: JSON_THROW_ON_ERROR);
	}
}
