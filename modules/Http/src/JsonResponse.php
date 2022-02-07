<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Stream\StringStream;
use Elephox\Support\MimeType;
use JetBrains\PhpStorm\Immutable;
use JsonException;

#[Immutable]
class JsonResponse extends Response
{
	/**
	 * @throws JsonException
	 */
	public function __construct(
		public readonly array $data,
		ResponseCode $responseCode = ResponseCode::OK,
		?Contract\HeaderMap $headers = null
	) {
		$json = $this->getJson();
		$headers ??= new HeaderMap();

		parent::__construct(
			AbstractMessageBuilder::DefaultProtocolVersion,
			$headers,
			new StringStream($json),
			$responseCode,
			MimeType::Applicationjson
		);
	}

	/**
	 * @throws \JsonException
	 */
	public function getJson(): string
	{
		return json_encode($this->data, JSON_THROW_ON_ERROR);
	}
}
