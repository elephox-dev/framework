<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Support\MimeType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Elephox\Http\Response
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\ObjectMap
 * @covers \Elephox\Http\ResponseHeaderMap
 * @covers \Elephox\Text\Regex
 * @covers \Elephox\Http\ResponseCode
 * @covers \Elephox\Http\HeaderName
 * @covers \Elephox\Support\MimeType
 * @covers \Elephox\Http\CustomResponseCode
 * @covers \Elephox\Http\InvalidResponseCodeMessageException
 */
class ResponseTest extends TestCase
{
	public function testConstructor(): void
	{
		$response = new Response(nullnull);
		self::assertEquals(200, $response->getResponseCode()->getCode());
		self::assertEquals('OK', $response->getResponseCode()->getMessage());
		self::assertEmpty($response->getHeaderMap()->asArray());
		self::assertNull($response->getContent());
	}

	public function testFromString(): void
	{
		$response = Response::fromString("HTTP/1.1 200 OK\n\n");
		self::assertEquals(ResponseCode::OK, $response->getResponseCode());
		self::assertEquals('1.1', $response->getProtocolVersion());
		self::assertEmpty($response->getHeaderMap()->asArray());

		$response->withResponseCode(ResponseCode::NotFound);
		$response->setContent('<h1>404 Not Found</h1>', MimeType::Texthtml);
		self::assertEquals(ResponseCode::NotFound, $response->getResponseCode());
	}

	public function testFromStringInvalidFormat(): void
	{
		$this->expectException(InvalidArgumentException::class);

		Response::fromString("No HTTP Message");
	}

	public function testWithJson(): void
	{
		$response = Response::withJson([
			'foo' => 'bar',
			'baz' => 'qux',
		]);

		self::assertEquals(ResponseCode::OK, $response->getResponseCode());
		self::assertEquals('{"foo":"bar","baz":"qux"}', $response->getContent());
		self::assertEquals([MimeType::Applicationjson->getValue()], $response->getHeaderMap()->get(HeaderName::ContentType));
	}

	public function testWithInvalidJson(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$a = new stdClass();
		$b = new stdClass();
		$a->b = $b;
		$b->a = $a;

		Response::withJson($a);
	}

	public function testWithJsonConvertibleContent(): void
	{
		$map = new ArrayMap([
			'foo' => 'bar',
			'baz' => 'qux',
		]);
		$response = Response::withJson($map);

		self::assertEquals(200, $response->getResponseCode()->getCode());
		self::assertEquals('{"foo":"bar","baz":"qux"}', $response->getContent());
	}

	public function testWithJsonNull(): void
	{
		$response = Response::withJson();

		self::assertEquals("OK", $response->getResponseCode()->getMessage());
		self::assertEquals(null, $response->getContent());
	}

	public function testCustomResponseCode(): void
	{
		$response = Response::fromString("HTTP/1.1 420 Blaze it\n\n");
		self::assertEquals(420, $response->getResponseCode()->getCode());
		self::assertEquals("Blaze it", $response->getResponseCode()->getMessage());
	}

	public function testInvalidCustomResponseCodeMessage(): void
	{
		$this->expectException(InvalidResponseCodeMessageException::class);

		Response::fromString("HTTP/1.1 999  \n\n");
	}

	public function testInvalidCustomResponseCode(): void
	{
		$this->expectException(InvalidArgumentException::class);

		Response::fromString("HTTP/1.1  test\n\n");
	}

	public function testMimeTypeGetsSet(): void
	{
		$response = Response::fromString("HTTP/1.1 200 OK\n\n");
		self::assertFalse($response->getHeaderMap()->has(HeaderName::ContentType));
		$response->setContent('<h1>404 Not Found</h1>', MimeType::Texthtml);
		self::assertEquals([MimeType::Texthtml->getValue()], $response->getHeaderMap()->get(HeaderName::ContentType));
	}

	public function testResponseCannotContainRequestOnlyHeaders(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new Response(protocolVersion: ['Host' => 'foo'], headers: ['Host' => 'foo'], body: null);
	}
}
