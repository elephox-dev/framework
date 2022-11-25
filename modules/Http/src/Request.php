<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\OOR\Casing;
use InvalidArgumentException;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

#[Immutable]
class Request extends AbstractMessage implements Contract\Request
{
	#[Pure]
	public static function build(): Contract\RequestBuilder
	{
		return new RequestBuilder();
	}

	#[Pure]
	public function __construct(
		string $protocolVersion,
		Contract\HeaderMap $headers,
		StreamInterface $body,
		public readonly Contract\RequestMethod $method,
		public readonly Url $url,
	) {
		parent::__construct($protocolVersion, $headers, $body);
	}

	#[Pure]
	public function with(): Contract\RequestBuilder
	{
		/** @psalm-suppress ImpureMethodCall */
		return new RequestBuilder(
			$this->protocolVersion,
			new HeaderMap($this->headers->toArray()),
			$this->body,
			$this->method,
			$this->url,
		);
	}

	#[Pure]
	public function getRequestMethod(): Contract\RequestMethod
	{
		return $this->method;
	}

	#[Pure]
	public function getUrl(): Url
	{
		return $this->url;
	}

	#[Pure]
	public function getRequestTarget(): string
	{
		return (string) $this->getUrl();
	}

	#[Pure]
	public function withRequestTarget($requestTarget): never
	{
		throw new RuntimeException(__METHOD__ . ' is not implemented');
	}

	#[Pure]
	public function withMethod($method): static
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($method)) {
			throw new InvalidArgumentException("Expected type 'string', but got " . get_debug_type($method));
		}

		if (Casing::toUpper($method) === $method) {
			$requestMethod = RequestMethod::tryFrom(Casing::toUpper($method));
		}

		$requestMethod ??= new CustomRequestMethod($method);

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->requestMethod($requestMethod)->get();
	}

	#[Pure]
	public function getUri(): UriInterface
	{
		return $this->url;
	}

	#[Pure]
	public function withUri(UriInterface $uri, $preserveHost = false): static
	{
		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->requestUrl(Url::fromString((string) $uri), $preserveHost)->get();
	}

	#[Pure]
	public function getMethod(): string
	{
		return $this->getRequestMethod()->getValue();
	}
}
