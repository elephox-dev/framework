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
 * @covers \Elephox\Http\ResponseHeaderMap
 * @covers \Elephox\Text\Regex
 * @covers \Elephox\Http\ResponseCode
 * @covers \Elephox\Http\HeaderName
 * @covers \Elephox\Support\MimeType
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
}
