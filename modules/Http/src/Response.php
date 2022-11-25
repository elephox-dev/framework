<?php
declare(strict_types=1);

namespace Elephox\Http;

use AssertionError;
use InvalidArgumentException;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Psr\Http\Message\StreamInterface;
use Throwable;
use ValueError;

#[Immutable]
class Response extends AbstractMessage implements Contract\Response
{
	#[Pure]
	public static function build(): ResponseBuilder
	{
		return new ResponseBuilder();
	}

	#[Pure]
	public function __construct(
		string $protocolVersion,
		Contract\HeaderMap $headers,
		StreamInterface $body,
		public readonly ResponseCode $responseCode,
		public readonly ?Throwable $exception,
	) {
		parent::__construct($protocolVersion, $headers, $body);
	}

	#[Pure]
	public function with(): ResponseBuilder
	{
		/** @psalm-suppress ImpureMethodCall */
		return new ResponseBuilder(
			$this->protocolVersion,
			new HeaderMap($this->headers->toArray()),
			$this->body,
			$this->responseCode,
		);
	}

	#[Pure]
	public function getResponseCode(): ResponseCode
	{
		return $this->responseCode;
	}

	#[Pure]
	public function getException(): ?Throwable
	{
		return $this->exception;
	}

	public function getStatusCode(): int
	{
		return $this->getResponseCode()->value;
	}

	public function withStatus($code, $reasonPhrase = ''): static
	{
		try {
			/** @psalm-suppress DocblockTypeContradiction */
			if (!is_int($code)) {
				throw new InvalidArgumentException("Expected type 'int', but got " . get_debug_type($code));
			}

			if ($code < 100 || $code > 599) {
				throw new InvalidArgumentException('Expected code to be in range 100-599 (inclusive), but got ' . $code);
			}

			/** @psalm-suppress DocblockTypeContradiction */
			if (!is_string($reasonPhrase)) {
				throw new InvalidArgumentException("Expected type 'string', but got " . get_debug_type($reasonPhrase));
			}

			if ($reasonPhrase !== '') {
				/** @psalm-suppress ImpureFunctionCall */
				trigger_error('non-standard $reasonPhrase values are not stored in the response');
			}

			/**
			 * @psalm-suppress ImpureMethodCall
			 *
			 * @var static
			 */
			return $this->with()->responseCode(ResponseCode::from($code))->get();
		} catch (ValueError|AssertionError $e) {
			throw new InvalidArgumentException('Error settings status code: ' . $e->getMessage(), previous: $e);
		}
	}

	public function getReasonPhrase(): string
	{
		return $this->getResponseCode()->getReasonPhrase();
	}
}
