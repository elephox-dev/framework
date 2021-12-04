<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

interface HttpMessage extends MessageInterface
{
	public function getHeaderMap(): ReadonlyHeaderMap;

	public function withoutBody(): self;

	public function getProtocolVersion(): string;

	public function withProtocolVersion($version): self;

	public function getHeaders(): array;

	public function hasHeader($name): bool;

	public function getHeader($name): array;

	public function getHeaderLine($name): string;

	public function withHeader($name, $value): self;

	public function withAddedHeader($name, $value): self;

	public function withoutHeader($name): self;

	public function getBody(): StreamInterface;

	public function withBody(StreamInterface $body): self;
}
