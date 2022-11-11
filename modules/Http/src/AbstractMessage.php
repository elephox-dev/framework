<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\HeaderMap;
use Elephox\Http\Contract\Message;
use Elephox\Stream\Contract\Stream;
use Elephox\Stream\Psr7Stream;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\StreamInterface;

#[Immutable]
abstract class AbstractMessage implements Message
{
	use DerivesContentTypeFromHeaderMap;

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
	public function withProtocolVersion($version): static
	{
		assert(is_string($version));

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var static
		 */
		return $this->with()->protocolVersion($version)->get();
	}

	#[Pure]
	public function getHeaderMap(): HeaderMap
	{
		return $this->headers;
	}

	#[Pure]
	public function getHeaders(): array
	{
		/** @psalm-suppress ImpureMethodCall */
		return $this->headers->toArray();
	}

	#[Pure]
	public function hasHeader($name)
	{
		assert(is_string($name));

		/** @psalm-suppress ImpureMethodCall */
		return $this->headers->containsKey($name);
	}

	#[Pure]
	public function getHeader($name): array
	{
		assert(is_string($name));

		if (!$this->hasHeader($name)) {
			return [];
		}

		/** @psalm-suppress ImpureMethodCall */
		return $this->headers->get($name);
	}

	#[Pure]
	public function getHeaderLine($name): string
	{
		return implode(',', $this->getHeader($name));
	}

	#[Pure]
	public function withHeader($name, $value): static
	{
		assert(is_string($name));
		assert(is_string($value) || array_is_list($value));

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var static
		 */
		return $this->with()->header($name, $value)->get();
	}

	#[Pure]
	public function withAddedHeader($name, $value): static
	{
		assert(is_string($name));
		assert(is_string($value) || array_is_list($value));

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var static
		 */
		return $this->with()->addHeader($name, $value)->get();
	}

	#[Pure]
	public function withoutHeader($name)
	{
		assert(is_string($name));

		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var static
		 */
		return $this->with()->removeHeader($name)->get();
	}

	#[Pure]
	public function getBody(): Stream
	{
		return $this->body;
	}

	#[Pure]
	public function withBody(StreamInterface $body): static
	{
		/**
		 * @psalm-suppress ImpureMethodCall
		 * @var static
		 */
		return $this->with()->body(new Psr7Stream($body))->get();
	}
}
