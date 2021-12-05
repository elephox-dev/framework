<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\OffsetNotFoundException;
use Elephox\Collection\ArrayList;
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

		/**
		 * @var Contract\RequestMethod|null $requestMethod
		 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
		 */
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
			$contentLength = (int)$headerMap->get(HeaderName::ContentLength);
		} catch (OffsetNotFoundException) {
			$contentLength = 0;
		}

		if ($contentLength > 0) {
			$body = new ResourceStream(fopen('php://input', 'rb'));
		} else {
			$body = new EmptyStream();
		}

		return new self($version, $requestMethod, $parsedUri, $body, $headerMap);
	}

	final private function __construct(string $protocolVersion, private Contract\RequestMethod $method, private UriInterface $url, StreamInterface $body, Contract\RequestHeaderMap $headers)
	{
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

	#[Pure] public function getHeaderMap(): Contract\RequestHeaderMap
	{
		return $this->headers->asRequestHeaders();
	}

	#[Pure] public function getRequestTarget(): string
	{
		return (string)$this->url;
	}

	public function withoutBody(): static
	{
		return new static($this->protocolVersion, clone $this->method, clone $this->url, new EmptyStream(), (clone $this->headers)->asRequestHeaders());
	}

	public function withProtocolVersion($version): static
	{
		return new static($version, clone $this->method, clone $this->url, clone $this->body, (clone $this->headers)->asRequestHeaders());
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

	public function withBody(StreamInterface $body): static
	{
		return new static($this->protocolVersion, clone $this->method, clone $this->url, $body, (clone $this->headers)->asRequestHeaders());
	}

	public function withHeaderName(Contract\HeaderName $name, array|string $value): static
	{
		$headers = (clone $this->headers)->asRequestHeaders();
		$headers->put($name, $value);

		return new static($this->protocolVersion, clone $this->method, clone $this->url, clone $this->body, $headers);
	}

	public function withAddedHeaderName(Contract\HeaderName $name, array|string $value): static
	{
		$headers = (clone $this->headers)->asRequestHeaders();

		if ($headers->has($name)) {
			/** @var ArrayList<string> $values */
			$values = $headers->get($name);
			if (is_array($value)) {
				$values->addAll($value);
			} else {
				$values->add($value);
			}
		} else {
			$values = new ArrayList([$value]);
		}

		/** @var iterable<string> $values */
		$headers->put($name, $values);

		return new static($this->protocolVersion, clone $this->method, clone $this->url, clone $this->body, $headers);
	}

	public function withoutHeaderName(Contract\HeaderName $name): static
	{
		$headers = (clone $this->headers)->asRequestHeaders();

		if ($headers->has($name)) {
			$headers->remove($name);
		}

		return new static($this->protocolVersion, clone $this->method, clone $this->url, clone $this->body, $headers);
	}

	public function withRequestMethod(Contract\RequestMethod $method): static
	{
		return new static($this->protocolVersion, $method, clone $this->url, clone $this->body, (clone $this->headers)->asRequestHeaders());
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

		/**
		 * @var Contract\RequestMethod|null $requestMethod
		 * @psalm-suppress UndefinedMethod Until vimeo/psalm#6429 is fixed.
		 */
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

		return new static($this->protocolVersion, clone $this->method, $uri, clone $this->body, $headers);
	}
}
