<?php
declare(strict_types=1);

namespace Elephox\Http;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\Request
 * @covers \Elephox\Collection\ObjectMap
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Http\RequestMethod
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Http\UrlScheme
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\RequestHeaderMap
 * @covers \Elephox\Http\HeaderName
 * @covers \Elephox\Http\CustomRequestMethod
 * @covers \Elephox\Collection\InvalidOffsetException
 * @covers \Elephox\Collection\OffsetNotFoundException
 */
class RequestTest extends TestCase
{
	public function testConstructorStrings(): void
	{
		$request = new Request("GET", "/test");

		self::assertEquals(RequestMethod::GET, $request->getMethod());
		self::assertEquals('/test', $request->getUrl()->getPath());
		self::assertCount(0, $request->getHeaders()->asArray());
	}

	public function testConstructorMethodObject(): void
	{
		$request = new Request(RequestMethod::POST, "/test");

		self::assertEquals(RequestMethod::POST, $request->getMethod());
		self::assertEquals('/test', $request->getUrl()->getPath());
		self::assertCount(0, $request->getHeaders()->asArray());
	}

	public function testConstructorUrlObject(): void
	{
		$request = new Request("DELETE", Url::fromString("/test"));

		self::assertEquals(RequestMethod::DELETE, $request->getMethod());
		self::assertEquals('/test', $request->getUrl()->getPath());
		self::assertCount(0, $request->getHeaders()->asArray());
	}

	public function testConstructorHeaderMap(): void
	{
		$headers = new HeaderMap();
		$headers->put(HeaderName::Host, ["test"]);

		$request = new Request("GET", "/test", $headers);

		self::assertEquals(RequestMethod::GET, $request->getMethod());
		self::assertEquals('/test', $request->getUrl()->getPath());
		self::assertCount(1, $request->getHeaders()->asArray());
		self::assertEquals("test", $request->getHeaders()->get(HeaderName::Host));
	}

	public function testFromGlobals(): void
	{
		$_SERVER['REQUEST_METHOD'] = "GET";
		$_SERVER['REQUEST_URI'] = "/";
		$_SERVER['HTTP_ACCEPT'] = "application/json";

		$request = Request::fromGlobals();

		self::assertEquals(RequestMethod::GET, $request->getMethod());
		self::assertFalse($request->getMethod()->canHaveBody());
		self::assertEquals('/', $request->getUrl()->toString());
		self::assertTrue($request->shouldFollowRedirects());
	}

	public function testFromGlobalsCustomRequestMethod(): void
	{
		$_SERVER['REQUEST_METHOD'] = "NEW";
		$_SERVER['REQUEST_URI'] = "/";

		$request = Request::fromGlobals();

		self::assertInstanceOf(CustomRequestMethod::class, $request->getMethod());
		self::assertEquals("NEW", $request->getMethod()->getValue());
		self::assertTrue($request->getMethod()->canHaveBody());
		self::assertEquals('/', $request->getUrl()->toString());
	}

	public function testFromGlobalsInvalid(): void
	{
		unset($_SERVER['REQUEST_METHOD']);

		$this->expectException(Exception::class);

		Request::fromGlobals();
	}

	public function testInvalidRequestMethodBody(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new Request(RequestMethod::GET, "/", body: "test");
	}
}
