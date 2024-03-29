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
		$target = $this->getUrl()->getPath();
		if ($target === '') {
			$target = '/';
		}

		$query = $this->getUrl()->getQuery();
		if ($query !== '') {
			$target .= '?' . $query;
		}

		return $target;
	}

	#[Pure]
	public function withRequestTarget(string $requestTarget): never
	{
		throw new RuntimeException(__METHOD__ . ' is not implemented');
	}

	#[Pure]
	public function withMethod(string $method): static
	{
		if ($method === '') {
			throw new InvalidArgumentException('Expected non-empty-string, but got an empty string instead.');
		}

		$requestMethod = null;
		if (Casing::toUpper($method) === $method) {
			/** @var \Elephox\Http\Contract\RequestMethod $requestMethod */
			$requestMethod = RequestMethod::tryFrom($method);
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
	public function withUri(UriInterface $uri, bool $preserveHost = false): static
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
