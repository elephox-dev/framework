<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Elephox\Collection\Contract\ReadonlyList;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

interface HttpMessage extends MessageInterface
{
	public function getHeaderMap(): ReadonlyHeaderMap;

	public function withoutBody(): static;

	public function getProtocolVersion(): string;

	public function withProtocolVersion($version): static;

	public function getHeaders(): array;

	public function hasHeader($name): bool;

	public function hasHeaderName(HeaderName $name): bool;

	/**
	 * @param string $name
	 * @return array<string>
	 */
	public function getHeader($name): array;

	/**
	 * @param HeaderName $name
	 * @return ReadonlyList<string>
	 */
	public function getHeaderName(HeaderName $name): ReadonlyList;

	public function getHeaderLine($name): string;

	public function withHeader($name, $value): static;

	public function withAddedHeader($name, $value): static;

	public function withoutHeader($name): static;

	/**
	 * @param HeaderName $name
	 * @param string|iterable<string> $value
	 * @return static
	 */
	public function withHeaderName(HeaderName $name, string|iterable $value): static;

	/**
	 * @param HeaderName $name
	 * @param string|iterable<string> $value
	 * @return static
	 */
	public function withAddedHeaderName(HeaderName $name, string|iterable $value): static;

	public function withoutHeaderName(HeaderName $name): static;

	public function getBody(): StreamInterface;

	public function withBody(StreamInterface $body): static;

	public function withHeaderMap(HeaderMap $map): static;
}
