<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Files\Contract\File;
use Elephox\Stream\ResourceStream;
use Elephox\Stream\StringStream;
use Elephox\Support\CustomMimeType;
use Mimey\MimeTypeInterface;

trait GeneratesResponses
{
	private function getDefaultBuilder(): ResponseBuilder
	{
		return Response::build();
	}

	/**
	 * @throws \JsonException
	 */
	private function jsonResponse(array $data, ?ResponseCode $responseCode = null): Contract\ResponseBuilder
	{
		return $this->getDefaultBuilder()
			->responseCode($responseCode ?? ResponseCode::OK)
			->jsonBody($data);
	}

	private function stringResponse(string $data, ?ResponseCode $responseCode = null, ?MimeTypeInterface $mimeType = null): Contract\ResponseBuilder
	{
		return $this->getDefaultBuilder()
			->responseCode($responseCode ?? ResponseCode::OK)
			->contentType($mimeType)
			->body(new StringStream($data));
	}

	private function resourceResponse(mixed $resource, ?ResponseCode $responseCode = null, ?MimeTypeInterface $mimeType = null): Contract\ResponseBuilder
	{
		return $this->getDefaultBuilder()
			->responseCode($responseCode ?? ResponseCode::OK)
			->contentType($mimeType ?? CustomMimeType::fromFile($resource))
			->resourceBody($resource);
	}

	private function fileResponse(string|File $path, ?ResponseCode $responseCode = null, ?MimeTypeInterface $mimeType = null): Contract\ResponseBuilder
	{
		$path = $path instanceof File ? $path->getPath() : $path;

		if (!file_exists($path)) {
			return $this->getDefaultBuilder()->responseCode($responseCode ?? ResponseCode::NotFound);
		}

		return $this->getDefaultBuilder()
			->responseCode($responseCode ?? ResponseCode::OK)
			->contentType($mimeType ?? CustomMimeType::fromFile($path))
			->body(ResourceStream::fromFile($path));
	}
}