<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Mimey\MimeType;
use PHPUnit\Framework\TestCase;
use JsonException;

/**
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\AbstractMessage
 * @covers \Elephox\Http\AbstractMessageBuilder
 * @covers \Elephox\Http\Response
 * @covers \Elephox\Http\ResponseBuilder
 * @covers \Elephox\Http\ResponseCode
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Http\GeneratesResponses
 * @covers \Elephox\Support\CustomMimeType
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Files\File
 * @covers \Elephox\Files\AbstractFilesystemNode
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\OOR\Str
 * @covers \Elephox\Http\HeaderName
 *
 * @internal
 */
final class GeneratesResponsesTest extends TestCase
{
	use GeneratesResponses;

	public function testGetDefaultBuilder(): void
	{
		$builder = $this->getDefaultBuilder();
		$builder->responseCode(ResponseCode::OK);

		$response = $builder->get();
		self::assertSame(AbstractMessageBuilder::DefaultProtocolVersion, $response->getProtocolVersion());
	}

	/**
	 * @throws JsonException
	 */
	public function testJsonResponse(): void
	{
		$response = $this->jsonResponse(['foo' => 'bar'])->get();
		self::assertSame(MimeType::ApplicationJson, $response->getContentType());
		self::assertSame('{"foo":"bar"}', $response->getBody()->getContents());
	}

	public function testStringResponse(): void
	{
		$response = $this->stringResponse('Hello World')->get();
		self::assertSame(MimeType::TextPlain, $response->getContentType());
		self::assertSame('Hello World', $response->getBody()->getContents());
	}

	public function testResourceResponse(): void
	{
		$resource = fopen('php://memory', 'wb+');
		fwrite($resource, 'Hello World');

		$response = $this->resourceResponse($resource)->get();
		self::assertSame(function_exists('mime_content_type') ? MimeType::TextPlain : MimeType::ApplicationOctetStream, $response->getContentType());

		fclose($resource);
	}

	public function testResourceResponseWithExtension(): void
	{
		$resource = fopen(__FILE__, 'rb');

		$response = $this->resourceResponse($resource)->get();
		self::assertSame(function_exists('mime_content_type') ? MimeType::TextXPhp : MimeType::ApplicationPhp, $response->getContentType());

		fclose($resource);
	}

	public function testFileResponse(): void
	{
		$response = $this->fileResponse(__FILE__)->get();
		// TODO: Add test for both cases in which mime_content_type exists and not
		self::assertSame(function_exists('mime_content_type') ? MimeType::TextXPhp : MimeType::ApplicationPhp, $response->getContentType());
		self::assertStringEqualsFile(__FILE__, $response->getBody()->getContents());
	}

	public function testFileNotFoundResponse(): void
	{
		$response = $this->fileResponse('/tmp/file-that-does-not-exist')->get();

		self::assertSame(ResponseCode::NotFound, $response->getResponseCode());
	}
}
