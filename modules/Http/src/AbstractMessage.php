<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\HeaderMap;
use Elephox\Http\Contract\Message;
use InvalidArgumentException;
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
		public readonly StreamInterface $body,
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
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($version)) {
			throw new InvalidArgumentException("Expected type 'string', but got " . get_debug_type($version));
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
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
	public function hasHeader($name): bool
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($name)) {
			throw new InvalidArgumentException("Expected type 'string', but got " . get_debug_type($name));
		}

		if (empty($name)) {
			throw new InvalidArgumentException('Cannot use the empty string as a header name');
		}

		/** @psalm-suppress ImpureMethodCall */
		return $this->headers->containsKey($name);
	}

	#[Pure]
	public function getHeader($name): array
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($name)) {
			throw new InvalidArgumentException("Expected type 'string', but got " . get_debug_type($name));
		}

		if (empty($name)) {
			throw new InvalidArgumentException('Cannot use the empty string as a header name');
		}

		if (!$this->hasHeader($name)) {
			return [];
		}

		/** @psalm-suppress ImpureMethodCall */
		return $this->headers->get($name);
	}

	#[Pure]
	public function getHeaderLine($name): string
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($name)) {
			throw new InvalidArgumentException("Expected type 'string', but got " . get_debug_type($name));
		}

		return implode(',', $this->getHeader($name));
	}

	#[Pure]
	public function withHeader($name, $value): static
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($name)) {
			throw new InvalidArgumentException("Expected type 'string', but got " . get_debug_type($name));
		}

		if (empty($name)) {
			throw new InvalidArgumentException('Cannot use the empty string as a header name');
		}

		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($value) && !is_array($value)) {
			throw new InvalidArgumentException("Expected type 'string' or 'array', but got " . get_debug_type($value));
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->header($name, $value)->get();
	}

	#[Pure]
	public function withAddedHeader($name, $value): static
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($name)) {
			throw new InvalidArgumentException("Expected type 'string', but got " . get_debug_type($name));
		}

		if (empty($name)) {
			throw new InvalidArgumentException('Cannot use the empty string as a header name');
		}

		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($value) && !is_array($value)) {
			throw new InvalidArgumentException("Expected type 'string' or 'array', but got " . get_debug_type($value));
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->addedHeader($name, $value)->get();
	}

	#[Pure]
	public function withoutHeader($name): static
	{
		/** @psalm-suppress DocblockTypeContradiction */
		if (!is_string($name)) {
			throw new InvalidArgumentException("Expected type 'string', but got " . get_debug_type($name));
		}

		if (empty($name)) {
			throw new InvalidArgumentException('Cannot use the empty string as a header name');
		}

		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->removedHeader($name)->get();
	}

	#[Pure]
	public function getBody(): StreamInterface
	{
		return $this->body;
	}

	#[Pure]
	public function withBody(StreamInterface $body): static
	{
		/**
		 * @psalm-suppress ImpureMethodCall
		 *
		 * @var static
		 */
		return $this->with()->body($body)->get();
	}
}
