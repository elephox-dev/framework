<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Support\MimeType;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

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
	public function testFromString(): void
	{
		$response = Response::fromString("HTTP/1.1 200 OK\n\n");
		$this->assertEquals(ResponseCode::Ok, $response->getCode());
		$this->assertEquals('1.1', $response->getHttpVersion());
		$this->assertEmpty($response->getHeaders()->asArray());

		$response->setCode(ResponseCode::NotFound);
		$response->setContent('<h1>404 Not Found</h1>', MimeType::Texthtml);
		$this->assertEquals(ResponseCode::NotFound, $response->getCode());
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

		$this->assertEquals(ResponseCode::Ok, $response->getCode());
		$this->assertEquals('{"foo":"bar","baz":"qux"}', $response->getContent());
		$this->assertEquals(MimeType::Applicationjson->getValue(), $response->getHeaders()->get(HeaderName::ContentType));
	}

	public function testWithJsonConvertibleContent(): void
	{
		$map = new ArrayMap([
			'foo' => 'bar',
			'baz' => 'qux',
		]);
		$response = Response::withJson($map);

		$this->assertEquals(200, $response->getCode()->getCode());
		$this->assertEquals('{"foo":"bar","baz":"qux"}', $response->getContent());
	}

	public function testWithJsonNull(): void
	{
		$response = Response::withJson();

		$this->assertEquals("Ok", $response->getCode()->getMessage());
		$this->assertEquals(null, $response->getContent());
	}

	public function testCustomResponseCode(): void
	{
		$response = Response::fromString("HTTP/1.1 420 Blaze it\n\n");
		$this->assertEquals(420, $response->getCode()->getCode());
		$this->assertEquals("Blaze it", $response->getCode()->getMessage());
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
		$this->assertFalse($response->getHeaders()->has(HeaderName::ContentType));
		$response->setContent('<h1>404 Not Found</h1>', MimeType::Texthtml);
		$this->assertEquals(MimeType::Texthtml->getValue(), $response->getHeaders()->get(HeaderName::ContentType));
	}
}
