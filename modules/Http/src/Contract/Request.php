<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

interface Request extends HttpMessage, RequestInterface
{
	public function getUri(): Url;

	public function getHeaderMap(): RequestHeaderMap;

	public function withAddedHeaderName(HeaderName $name, string|array $value): self;

	public function withoutHeaderName(HeaderName $name): self;

	public function getRequestMethod(): RequestMethod;

	public function withRequestMethod(RequestMethod $method): self;

	public function getProtocolVersion(): string;

	public function withProtocolVersion($version): self;

	public function getRequestTarget(): string;

	public function withRequestTarget($requestTarget): self;

	public function getMethod(): string;

	public function withMethod($method): self;

	public function withUri(UriInterface $uri, $preserveHost = false): self;

	/**
	 * @return array<non-empty-string, list<string>>
	 */
	public function getHeaders(): array;

	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasHeader($name): bool;

	/**
	 * @param string $name
	 * @return list<string>
	 */
	public function getHeader($name): array;

	/**
	 * @param string $name
	 * @return string
	 */
	public function getHeaderLine($name): string;
}
