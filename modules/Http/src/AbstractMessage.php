<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\HeaderMap;
use Elephox\Http\Contract\Message;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
abstract class AbstractMessage implements Message
{
	#[Pure]
	public function __construct(
		public readonly string $protocolVersion,
		public readonly HeaderMap $headers,
		public readonly Stream $body,
	) {
	}

	#[Pure]
	public function getProtocolVersion(): string
	{
		return $this->protocolVersion;
	}

	#[Pure]
	public function getHeaderMap(): HeaderMap
	{
		return $this->headers;
	}

	#[Pure]
	public function getBody(): Stream
	{
		return $this->body;
	}
}
