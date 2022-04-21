<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Mimey\MimeType;
use Elephox\Stream\ResourceStream;
use Elephox\Stream\StringStream;
use Exception;
use PHPUnit\Framework\TestCase;
use JsonException;

/**
 * @covers \Elephox\Http\ResponseBuilder
 * @covers \Elephox\Http\Response
 * @covers \Elephox\Http\ResponseCode
 * @covers \Elephox\Mimey\MimeType
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\AbstractMessageBuilder
 * @covers \Elephox\Http\AbstractMessage
 * @covers \Elephox\Files\AbstractFilesystemNode
 * @covers \Elephox\Files\File
 * @covers \Elephox\Stream\ResourceStream
 *
 * @internal
 */
class ResponseBuilderTest extends TestCase
{
	public function testBuild(): void
	{
		$builder = Response::build();
		$builder->responseCode(ResponseCode::OK);
		$builder->contentType(MimeType::TextPlain);
		$builder->header('X-Foo', ['bar']);
		$builder->body(new StringStream('Hello World'));

		$response = $builder->get();

		static::assertEquals(ResponseCode::OK, $response->getResponseCode());
	}

	public function testGetter(): void
	{
		$builder = Response::build();
		$builder->protocolVersion('2.0');
		$builder->exception(new Exception('Test'));
		$builder->responseCode(ResponseCode::OK);
		$builder->contentType(MimeType::TextPlain);
		$builder->header('X-Foo', ['bar']);
		$builder->body(new StringStream('Hello World'));

		static::assertEquals('2.0', $builder->getProtocolVersion());
		static::assertEquals(ResponseCode::OK, $builder->getResponseCode());
		static::assertEquals(MimeType::TextPlain, $builder->getContentType());
		static::assertEquals(['bar'], $builder->getHeaderMap()?->get('X-Foo'));
		static::assertEquals('Hello World', $builder->getBody()?->getContents());
		static::assertEquals('Test', $builder->getException()?->getMessage());
	}

	/**
	 * @throws JsonException
	 */
	public function testBodies(): void
	{
		$builder = Response::build()->responseCode(ResponseCode::OK);

		$htmlResponse = $builder->get()->with()->htmlBody('html')->get();
		$fileResponse = $builder->get()->with()->fileBody(__FILE__)->get();
		$jsonResponse = $builder->get()->with()->jsonBody(['foo' => 'bar'])->get();

		static::assertEquals(MimeType::TextHtml, $htmlResponse->getMimeType());
		static::assertInstanceOf(StringStream::class, $htmlResponse->getBody());
		static::assertEquals(MimeType::ApplicationOctetStream, $fileResponse->getMimeType());
		static::assertInstanceOf(ResourceStream::class, $fileResponse->getBody());
		static::assertEquals(MimeType::ApplicationJson, $jsonResponse->getMimeType());
		static::assertInstanceOf(StringStream::class, $jsonResponse->getBody());
	}
}
