<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Files\Contract\File as FileContract;
use Elephox\Files\File;
use Elephox\Mimey\MimeType;
use Elephox\Mimey\MimeTypeInterface;
use Elephox\Stream\StringStream;
use Elephox\Support\CustomMimeType;
use JetBrains\PhpStorm\Pure;
use JsonException;
use RuntimeException;

trait GeneratesResponses
{
	#[Pure]
	private function getDefaultBuilder(): ResponseBuilder
	{
		return Response::build();
	}

	/**
	 * @throws JsonException
	 *
	 * @param ?ResponseCode $responseCode
	 */
	private function jsonResponse(array $data, ?ResponseCode $responseCode = null): Contract\ResponseBuilder
	{
		return $this->getDefaultBuilder()
			->responseCode($responseCode ?? ResponseCode::OK)
			->jsonBody($data)
		;
	}

	private function stringResponse(string $data, ?ResponseCode $responseCode = null, ?MimeTypeInterface $mimeType = null): Contract\ResponseBuilder
	{
		return $this->getDefaultBuilder()
			->responseCode($responseCode ?? ResponseCode::OK)
			->contentType($mimeType ?? MimeType::TextPlain)
			->body(new StringStream($data))
		;
	}

	private function resourceResponse(mixed $resource, ?ResponseCode $responseCode = null, ?MimeTypeInterface $mimeType = null): Contract\ResponseBuilder
	{
		try {
			$mimeType ??= CustomMimeType::fromFile($resource);
		} catch (RuntimeException) {
			$mimeType = MimeType::ApplicationOctetStream;
		}

		return $this->getDefaultBuilder()
			->responseCode($responseCode ?? ResponseCode::OK)
			->contentType($mimeType)
			->resourceBody($resource)
		;
	}

	private function fileResponse(string|FileContract $file, ?ResponseCode $responseCode = null, ?MimeTypeInterface $mimeType = null): Contract\ResponseBuilder
	{
		$file = $file instanceof FileContract ? $file : new File($file);

		if (!$file->exists()) {
			return $this->getDefaultBuilder()->responseCode($responseCode ?? ResponseCode::NotFound);
		}

		return $this->getDefaultBuilder()
			->responseCode($responseCode ?? ResponseCode::OK)
			->contentType($mimeType ?? $file->getMimeType() ?? CustomMimeType::fromFile($file->getPath()))
			->body($file->stream())
		;
	}
}
