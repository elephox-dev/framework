<?php
declare(strict_types=1);

namespace Elephox\Http;

use Exception;
use InvalidArgumentException;
use LogicException;
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
 * @covers \Elephox\Http\CustomHeaderName
 * @covers \Elephox\Http\AbstractHttpMessage
 * @covers \Elephox\Http\EmptyStream
 * @covers \Elephox\Collection\ArrayList
 */
class RequestTest extends TestCase
{
	public function testConstructorStrings(): void
	{
		$request = new Request("1.1", new RequestHeaderMap(), new EmptyStream(), RequestMethod::GET, Url::fromString("/test"));

		self::assertEquals(RequestMethod::GET, $request->getRequestMethod());
		self::assertEquals('/test', $request->getUri()->getPath());
		self::assertCount(0, $request->getHeaderMap()->asArray());
	}

	public function testConstructorMethodObject(): void
	{
		$request = new Request("1.1", new RequestHeaderMap(), new EmptyStream(), RequestMethod::POST, Url::fromString("/test"));

		self::assertEquals(RequestMethod::POST, $request->getRequestMethod());
		self::assertEquals('/test', $request->getUri()->getPath());
		self::assertCount(0, $request->getHeaderMap()->asArray());
	}

	public function testConstructorUrlObject(): void
	{
		$request = new Request("1.1", new RequestHeaderMap(), new EmptyStream(), RequestMethod::DELETE, Url::fromString("/test"));

		self::assertEquals(RequestMethod::DELETE, $request->getRequestMethod());
		self::assertEquals('/test', $request->getUri()->getPath());
		self::assertCount(0, $request->getHeaderMap()->asArray());
	}

	public function testConstructorHeaderMap(): void
	{
		$headers = new RequestHeaderMap();
		$headers->put(HeaderName::Host, ["test"]);

		$request = new Request("1.1", $headers, new EmptyStream(), RequestMethod::GET, Url::fromString("/test"));

		self::assertEquals(RequestMethod::GET, $request->getRequestMethod());
		self::assertEquals('/test', $request->getUri()->getPath());
		self::assertCount(1, $request->getHeaderMap()->asArray());
		self::assertEquals(["test"], $request->getHeaderMap()->get(HeaderName::Host)->asArray());
	}

	public function testFromGlobals(): void
	{
		$_SERVER['REQUEST_METHOD'] = "GET";
		$_SERVER['REQUEST_URI'] = "/";
		$_SERVER['HTTP_ACCEPT'] = "application/json";
		$_SERVER['HTTP_USER_AGENT'] = "test/1.0.0";
		$_SERVER['HTTP_X_CUSTOM'] = "custom-test";

		$request = Request::fromGlobals();

		self::assertEquals(RequestMethod::GET, $request->getRequestMethod());
		self::assertFalse($request->getRequestMethod()->canHaveBody());
		self::assertEquals('/', $request->getUri()->__toString());
		self::assertTrue($request->getHeaderMap()->has(HeaderName::Accept));
		self::assertTrue($request->getHeaderMap()->has(HeaderName::UserAgent));
		self::assertTrue($request->getHeaderMap()->anyKey(fn(\Elephox\Http\Contract\HeaderName $header) => $header->getValue() === "X-Custom"));
		self::assertEquals(["application/json"], $request->getHeaderMap()->get(HeaderName::Accept)->asArray());
		self::assertEquals(["test/1.0.0"], $request->getHeaderMap()->get(HeaderName::UserAgent)->asArray());
		self::assertEquals(["custom-test"], $request->getHeaderMap()->get("X-Custom")->asArray());

		unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['HTTP_ACCEPT'], $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_X_CUSTOM']);
	}

	public function testFromGlobalsCustomRequestMethod(): void
	{
		$_SERVER['REQUEST_METHOD'] = "NEW";
		$_SERVER['REQUEST_URI'] = "/";

		$request = Request::fromGlobals();

		self::assertInstanceOf(CustomRequestMethod::class, $request->getRequestMethod());
		self::assertEquals("NEW", $request->getRequestMethod()->getValue());
		self::assertTrue($request->getRequestMethod()->canHaveBody());
		self::assertEquals('/', $request->getUri()->__toString());

		unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
	}

	public function testFromGlobalsInvalidRequestMethod(): void
	{
		unset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
		$this->expectException(Exception::class);

		Request::fromGlobals();
	}

	public function testFromGlobalsInvalidRequestUri(): void
	{
		unset($_SERVER['REQUEST_URI']);
		$_SERVER['REQUEST_METHOD'] = "GET";

		$this->expectException(Exception::class);

		Request::fromGlobals();

		unset($_SERVER['REQUEST_METHOD']);
	}
}
