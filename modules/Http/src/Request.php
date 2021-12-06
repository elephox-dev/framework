<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\OffsetNotFoundException;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

class Request extends AbstractHttpMessage implements Contract\Request
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

		if (array_key_exists('CONTENT_TYPE', $_SERVER)) {
			/** @var mixed */
			$headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
		}

		if (array_key_exists('CONTENT_LENGTH', $_SERVER)) {
			/** @var mixed */
			$headers['Content-Length'] = $_SERVER['CONTENT_LENGTH'];
		}

		$headerMap = RequestHeaderMap::fromArray($headers);

		if (!array_key_exists("REQUEST_METHOD", $_SERVER) || empty($_SERVER["REQUEST_METHOD"])) {
			throw new RuntimeException("REQUEST_METHOD is not set.");
		}

		if (array_key_exists("SERVER_PROTOCOL", $_SERVER)) {
			/** @var non-empty-string $version */
			$version = $_SERVER['SERVER_PROTOCOL'];
		} else {
			$version = "1.1";
		}

		/** @var non-empty-string $method */
		$method = $_SERVER["REQUEST_METHOD"];

		$requestMethod = RequestMethod::tryFrom($method);
		if ($requestMethod === null) {
			$requestMethod = new CustomRequestMethod($method);
		}

		if (!array_key_exists("REQUEST_URI", $_SERVER)) {
			throw new RuntimeException("REQUEST_URI is not set.");
		}

		/** @var string $uri */
		$uri = $_SERVER["REQUEST_URI"];
		$parsedUri = Url::fromString($uri);

		try {
			$contentLength = (int)$headerMap->get(HeaderName::ContentLength)->first();
		} catch (OffsetNotFoundException) {
			$contentLength = 0;
		}

		if ($contentLength > 0) {
			$body = new ResourceStream(fopen('php://input', 'rb'));
		} else {
			$body = new EmptyStream();
		}

		return new self($version, $headerMap, $body, $requestMethod, $parsedUri);
	}

	final public function __construct(
		string $protocolVersion,
		Contract\RequestHeaderMap $headers,
		StreamInterface $body,
		private Contract\RequestMethod $method,
		private UriInterface $url
	) {
		parent::__construct($protocolVersion, $headers, $body);

		if (!$this->method->canHaveBody() && $body->getSize() > 0) {
			throw new InvalidArgumentException("Request method {$this->method->getValue()} cannot have a body.");
		}

		if ($this->headers->anyKey(static fn(Contract\HeaderName $name) => $name->isOnlyResponse())) {
			throw new InvalidArgumentException("Requests cannot contain headers reserved for responses only.");
		}
	}

	#[Pure] public function getUri(): UriInterface
	{
		return $this->url;
	}

	#[Pure] public function getRequestMethod(): Contract\RequestMethod
	{
		return $this->method;
	}

	#[Pure] public function getMethod(): string
	{
		return $this->method->getValue();
	}

	public function getHeaderMap(): Contract\RequestHeaderMap
	{
		return $this->headers->asRequestHeaders();
	}

	#[Pure] public function getRequestTarget(): string
	{
		return (string)$this->url;
	}

	public function withoutBody(): static
	{
		return new static($this->protocolVersion, (clone $this->headers)->asRequestHeaders(), new EmptyStream(), clone $this->method, clone $this->url);
	}

	public function withProtocolVersion($version): static
	{
		return new static($version, (clone $this->headers)->asRequestHeaders(), clone $this->body, clone $this->method, clone $this->url);
	}

	public function withHeader($name, $value): static
	{
		$headerName = HeaderMap::parseHeaderName($name);

		return $this->withHeaderName($headerName, $value);
	}

	public function withAddedHeader($name, $value): static
	{
		$headerName = HeaderMap::parseHeaderName($name);

		return $this->withHeaderName($headerName, $value);
	}

	public function withoutHeader($name): static
	{
		$headerName = HeaderMap::parseHeaderName($name);

		return $this->withoutHeaderName($headerName);
	}

	public function withHeaderMap(Contract\HeaderMap $map): static
	{
		return new static($this->protocolVersion, $map->asRequestHeaders(), clone $this->body, clone $this->method, clone $this->url);
	}

	public function withBody(StreamInterface $body): static
	{
		return new static($this->protocolVersion, (clone $this->headers)->asRequestHeaders(), $body, clone $this->method, clone $this->url);
	}

	public function withRequestMethod(Contract\RequestMethod $method): static
	{
		return new static($this->protocolVersion, (clone $this->headers)->asRequestHeaders(), clone $this->body, $method, clone $this->url);
	}

	public function withRequestTarget($requestTarget): static
	{
		if (!is_string($requestTarget)) {
			throw new InvalidArgumentException("Request target must be a string.");
		}

		$uri = Url::fromString($requestTarget);

		return $this->withUri($uri);
	}

	public function withMethod($method): static
	{
		if (empty($method)) {
			throw new InvalidArgumentException('Method cannot be empty.');
		}

		$requestMethod = RequestMethod::tryFrom($method);
		if ($requestMethod === null) {
			$requestMethod = new CustomRequestMethod($method);
		}

		return $this->withRequestMethod($requestMethod);
	}

	public function withUri(UriInterface $uri, $preserveHost = false): static
	{
		$headers = (clone $this->headers)->asRequestHeaders();
		if ($preserveHost) {
			$updateHostHeader = false;
			if ($headers->has(HeaderName::Host)) {
				$hostHeader = $headers->get(HeaderName::Host);
				if ($hostHeader->isEmpty()) {
					$updateHostHeader = true;
				}
			} else {
				$updateHostHeader = true;
			}

			if ($updateHostHeader && !empty($uri->getHost())) {
				$headers->put(HeaderName::Host, $uri->getHost());
			}
		}

		return new static($this->protocolVersion, $headers, clone $this->body, clone $this->method, $uri);
	}
}
