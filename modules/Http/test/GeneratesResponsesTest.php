<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Mimey\MimeType;
use PHPUnit\Framework\TestCase;

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
 */
class GeneratesResponsesTest extends TestCase
{
	use GeneratesResponses;

	public function testGetDefaultBuilder(): void
	{
		$builder = $this->getDefaultBuilder();
		$builder->responseCode(ResponseCode::OK);

		$response = $builder->get();
		self::assertEquals(AbstractMessageBuilder::DefaultProtocolVersion, $response->getProtocolVersion());
	}

	/**
	 * @throws \JsonException
	 */
	public function testJsonResponse(): void
	{
		$response = $this->jsonResponse(['foo' => 'bar'])->get();
		self::assertEquals(MimeType::ApplicationJson, $response->getMimeType());
		self::assertEquals('{"foo":"bar"}', $response->getBody()->getContents());
	}

	public function testStringResponse(): void
	{
		$response = $this->stringResponse('Hello World')->get();
		self::assertEquals(MimeType::TextPlain, $response->getMimeType());
		self::assertEquals('Hello World', $response->getBody()->getContents());
	}

	public function testResourceResponse(): void
	{
		$resource = fopen('php://memory', 'wb+');
		fwrite($resource, 'Hello World');

		$response = $this->resourceResponse($resource)->get();
		self::assertEquals(MimeType::ApplicationOctetStream, $response->getMimeType());

		fclose($resource);
	}

	public function testFileResponse(): void
	{
		$response = $this->fileResponse(__FILE__)->get();
		self::assertEquals(MimeType::TextXPhp, $response->getMimeType());
		self::assertEquals(file_get_contents(__FILE__), $response->getBody()->getContents());
	}

	public function testFileNotFoundResponse(): void
	{
		$response = $this->fileResponse("/tmp/file-that-does-not-exist")->get();

		self::assertEquals(ResponseCode::NotFound, $response->getResponseCode());
	}
}

//class MeGeneratesResponses
//{
//	use GeneratesResponses
//	{
//		getDefaultBuilder as public;
//		jsonResponse as public;
//		fileResponse as public;
//		resourceResponse as public;
//		stringResponse as public;
//	}
//}
