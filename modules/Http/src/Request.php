<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\OffsetNotFoundException;
use Elephox\Http\Contract\ReadonlyHeaderMap;
use Elephox\Http\HeaderName;
use Elephox\Http\RequestMethod;
use InvalidArgumentException;
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

	public function __construct(string $protocolVersion, private Contract\RequestMethod $method, private Contract\Url $url, StreamInterface $body, Contract\RequestHeaderMap $headers)
	{
		parent::__construct($protocolVersion, $headers, $body);

		if (!$this->method->canHaveBody() && $body->getSize() > 0) {
			throw new InvalidArgumentException("Request method {$this->method->getValue()} cannot have a body.");
		}

		if ($this->headers->anyKey(static fn(Contract\HeaderName $name) => $name->isOnlyResponse())) {
			throw new InvalidArgumentException("Requests cannot contain headers reserved for responses only.");
		}
	}

	public function getUri(): Contract\Url
	{
		return $this->url;
	}

	public function getRequestMethod(): Contract\RequestMethod
	{
		return $this->method;
	}

	public function withoutBody(): self
	{
		// TODO: Implement withoutBody() method.
	}

	public function withProtocolVersion($version): self
	{
		// TODO: Implement withProtocolVersion() method.
	}

	public function withHeader($name, $value): self
	{
		// TODO: Implement withHeader() method.
	}

	public function withAddedHeader($name, $value): self
	{
		// TODO: Implement withAddedHeader() method.
	}

	public function withoutHeader($name): self
	{
		// TODO: Implement withoutHeader() method.
	}

	public function withBody(StreamInterface $body): self
	{
		// TODO: Implement withBody() method.
	}

	public function withAddedHeaderName(HeaderName $name, array|string $value): self
	{
		// TODO: Implement withAddedHeaderName() method.
	}

	public function withoutHeaderName(HeaderName $name): self
	{
		// TODO: Implement withoutHeaderName() method.
	}

	public function withRequestMethod(RequestMethod $method): self
	{
		// TODO: Implement withRequestMethod() method.
	}

	public function getRequestTarget(): string
	{
		// TODO: Implement getRequestTarget() method.
	}

	public function withRequestTarget($requestTarget): self
	{
		// TODO: Implement withRequestTarget() method.
	}

	public function getMethod(): string
	{
		// TODO: Implement getMethod() method.
	}

	public function withMethod($method): self
	{
		// TODO: Implement withMethod() method.
	}

	public function withUri(UriInterface $uri, $preserveHost = false): self
	{
		// TODO: Implement withUri() method.
	}

	public function getHeaderMap(): Contract\RequestHeaderMap
	{
		// TODO: Implement getHeaderMap() method.
	}
}
