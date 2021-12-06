<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

interface Request extends HttpMessage, RequestInterface
{
	public function getUri(): UriInterface;

	public function getHeaderMap(): RequestHeaderMap;

	public function getRequestMethod(): RequestMethod;

	public function withRequestMethod(RequestMethod $method): static;

	public function getProtocolVersion(): string;

	public function withProtocolVersion($version): static;

	public function getRequestTarget(): string;

	public function withRequestTarget($requestTarget): static;

	public function getMethod(): string;

	public function withMethod($method): static;

	public function withUri(UriInterface $uri, $preserveHost = false): static;

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
