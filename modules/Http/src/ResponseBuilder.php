<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Stream\Contract\Stream;
use Elephox\Stream\EmptyStream;
use Elephox\Stream\StringStream;
use Elephox\Support\Contract\MimeType as MimeTypeContract;
use Elephox\Support\MimeType;
use JetBrains\PhpStorm\Pure;
use JsonException;
use LogicException;

/**
 * @psalm-consistent-constructor
 */
class ResponseBuilder extends AbstractMessageBuilder implements Contract\ResponseBuilder
{
	#[Pure]
	public function __construct(
		?string $protocolVersion = null,
		?Contract\HeaderMap $headers = null,
		?Stream $body = null,
		protected ?ResponseCode $responseCode = null,
		protected ?MimeTypeContract $mimeType = null
	) {
		parent::__construct($protocolVersion, $headers, $body);
	}

	public function responseCode(ResponseCode $responseCode): static
	{
		$this->responseCode = $responseCode;

		return $this;
	}

	public function contentType(?MimeTypeContract $mimeType): static
	{
		$this->mimeType = $mimeType;

		return $this;
	}

	/**
	 * @throws JsonException
	 */
	public function jsonBody(array $data): static
	{
		$json = json_encode($data, JSON_THROW_ON_ERROR);
		$jsonStream = new StringStream($json);
		return $this->body($jsonStream)->contentType(MimeType::Applicationjson);
	}

	public function get(): Contract\Response
	{
		return new Response(
			$this->protocolVersion ?? self::DefaultProtocolVersion,
			$this->headers ?? new HeaderMap(),
			$this->body ?? new EmptyStream(),
			$this->responseCode ?? throw new LogicException('Response code is not set.'),
			$this->mimeType
		);
	}
}
