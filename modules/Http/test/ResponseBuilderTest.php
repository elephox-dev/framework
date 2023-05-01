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
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\OOR\Casing
 * @covers \Elephox\Http\HeaderName
 *
 * @internal
 */
final class ResponseBuilderTest extends TestCase
{
	public function testBuild(): void
	{
		$builder = Response::build();
		$builder->responseCode(ResponseCode::OK);
		$builder->contentType(MimeType::TextPlain);
		$builder->header('X-Foo', ['bar']);
		$builder->body(new StringStream('Hello World'));

		$response = $builder->get();

		self::assertSame(ResponseCode::OK, $response->getResponseCode());
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

		self::assertSame('2.0', $builder->getProtocolVersion());
		self::assertSame(ResponseCode::OK, $builder->getResponseCode());
		self::assertSame(MimeType::TextPlain, $builder->getContentType());
		self::assertSame(['bar'], $builder->getHeaderMap()?->get('X-Foo'));
		self::assertSame('Hello World', $builder->getBody()?->getContents());
		self::assertSame('Test', $builder->getException()?->getMessage());
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

		self::assertSame(MimeType::TextHtml, $htmlResponse->getContentType());
		self::assertInstanceOf(StringStream::class, $htmlResponse->getBody());
		self::assertSame(MimeType::ApplicationOctetStream, $fileResponse->getContentType());
		self::assertInstanceOf(ResourceStream::class, $fileResponse->getBody());
		self::assertSame(MimeType::ApplicationJson, $jsonResponse->getContentType());
		self::assertInstanceOf(StringStream::class, $jsonResponse->getBody());
	}
}
