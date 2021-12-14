<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Stream\Contract\Stream;
use Elephox\Stream\LazyResourceStream;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use LogicException;
use RuntimeException;

/**
 * @psalm-consistent-constructor
 */
class Request extends AbstractMessage implements Contract\Request
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

		$body = new LazyResourceStream(static function () {
			$stream = fopen('php://input', 'rb');
			if ($stream === false) {
				throw new RuntimeException("Failed to open php://input.");
			}
			return $stream;
		});

		return new self($requestMethod, $parsedUri, $headerMap, $body, $version);
	}

	protected static function createHeaderMap(): Contract\RequestHeaderMap
	{
		return new RequestHeaderMap();
	}

	private Contract\Url $url;

	public function __construct(
		private Contract\RequestMethod $method = RequestMethod::GET,
		?Contract\Url                  $url = null,
		?Contract\RequestHeaderMap     $headers = null,
		?Stream                        $body = null,
		string                         $protocolVersion = "1.1",
		bool                           $inferHostHeader = true
	) {
		parent::__construct($headers, $body, $protocolVersion);

		$this->url = $url ?? Url::fromString("");

		if (!$this->getRequestMethod()->canHaveBody() && $this->getBody()->getSize() > 0) {
			throw new InvalidArgumentException("Request method {$this->getRequestMethod()->getValue()} cannot have a body.");
		}

		if ($inferHostHeader && !$this->getHeaderMap()->has(HeaderName::Host)) {
			$this->updateHostHeader();
		}

		if ($this->getHeaderMap()->anyKey(static fn(Contract\HeaderName $name) => $name->isOnlyResponse())) {
			throw new InvalidArgumentException("Requests cannot contain headers reserved for responses only.");
		}
	}

	#[Pure] public function getUri(): Contract\Url
	{
		return $this->url;
	}

	#[Pure] public function getRequestMethod(): Contract\RequestMethod
	{
		return $this->method;
	}

	public function getHeaderMap(): Contract\RequestHeaderMap
	{
		if (!$this->headers instanceof Contract\RequestHeaderMap) {
			throw new LogicException("Headers do not implement Contract\RequestHeaderMap.");
		}

		return $this->headers;
	}

	public function withProtocolVersion(string $version): static
	{
		if ($version === $this->protocolVersion) {
			return $this;
		}

		return new static($this->method, clone $this->url, (clone $this->headers)->asRequestHeaders(), clone $this->body, $version);
	}

	public function withHeaderMap(Contract\HeaderMap $map): static
	{
		if ($map === $this->headers) {
			return $this;
		}

		return new static($this->method, clone $this->url, $map->asRequestHeaders(), clone $this->body, $this->protocolVersion, false);
	}

	public function withBody(Stream $body): static
	{
		if ($body === $this->body) {
			return $this;
		}

		return new static($this->method, clone $this->url, (clone $this->headers)->asRequestHeaders(), $body, $this->protocolVersion);
	}

	public function withRequestMethod(Contract\RequestMethod $method): static
	{
		if ($method === $this->method) {
			return $this;
		}

		return new static($method, clone $this->url, (clone $this->headers)->asRequestHeaders(), clone $this->body, $this->protocolVersion);
	}

	public function withUrl(Contract\Url $url, bool $preserveHost = false): static
	{
		if ($url === $this->url) {
			return $this;
		}

		$newRequest = new static($this->method, $url, (clone $this->headers)->asRequestHeaders(), clone $this->body, $this->protocolVersion);

		if (!$preserveHost) {
			$newRequest->updateHostHeader();
		}

		return $newRequest;
	}

	protected function updateHostHeader(): void
	{
		$uri = $this->getUri();

		$host = $uri->getHost();
		if (empty($host)) {
			return;
		}

		$port = $uri->getPort();
		if ($port !== null) {
			$host .= ":" . $port;
		}

		$this->getHeaderMap()->put(HeaderName::Host, $host);
	}

	#[Pure] public function getUrl(): Contract\Url
	{
		return $this->url;
	}
}
