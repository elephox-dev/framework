<?php
declare(strict_types=1);

namespace Elephox\Http;

use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\StreamInterface;

abstract class AbstractHttpMessage implements Contract\HttpMessage
{
	#[Pure] public function __construct(
		protected string $protocolVersion,
		protected Contract\ReadonlyHeaderMap $headers,
		protected StreamInterface $body
	) {}

	public function getProtocolVersion(): string
	{
		return $this->protocolVersion;
	}

	public function getHeaders(): array
	{
		return $this->headers->asArray();
	}

	public function hasHeader($name): bool
	{
		return $this->headers->has($name);
	}

	public function getHeader($name): array
	{
		return $this->headers->get($name)->asArray();
	}

	public function getHeaderLine($name): string
	{
		return $this->headers->get($name)->join(',');
	}

	public function getBody(): StreamInterface
	{
		return $this->body;
	}
}
