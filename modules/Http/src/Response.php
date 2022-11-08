<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\HeaderMap;
use Elephox\Stream\Contract\Stream;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;
use Throwable;

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
		HeaderMap $headers,
		Stream $body,
		public readonly ResponseCode $responseCode,
		public readonly ?Throwable $exception,
	) {
		parent::__construct($protocolVersion, $headers, $body);
	}

	#[Pure]
	public function with(): ResponseBuilder
	{
		return new ResponseBuilder(
			$this->protocolVersion,
			$this->headers,
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
}
