<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\HeaderMap;
use Elephox\Stream\Contract\Stream;
use Elephox\Support\Contract\MimeType;
use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class Response extends AbstractMessage implements Contract\Response
{
	#[Pure]
	public function __construct(
		string $protocolVersion,
		HeaderMap $headers,
		Stream $body,
		public readonly ResponseCode $responseCode,
		public readonly ?MimeType $mimeType
	) {
		parent::__construct($protocolVersion, $headers, $body);
	}

	#[Pure]
	public function with(): Contract\ResponseBuilder
	{
		return new ResponseBuilder();
	}

	#[Pure]
	public function getResponseCode(): ResponseCode
	{
		return $this->responseCode;
	}

	#[Pure]
	public function getMimeType(): ?MimeType
	{
		return $this->mimeType;
	}
}
