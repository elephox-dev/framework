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
		$_SERVER['HTTP_USER_AGENT'] = "test/1.0.0";
		$_SERVER['HTTP_X_CUSTOM'] = "custom-test";

		$request = Request::fromGlobals();

		self::assertEquals(RequestMethod::GET, $request->getMethod());
		self::assertFalse($request->getMethod()->canHaveBody());
		self::assertEquals('/', $request->getUrl()->__toString());
		self::assertTrue($request->shouldFollowRedirects());
		self::assertTrue($request->getHeaders()->has(HeaderName::Accept));
		self::assertTrue($request->getHeaders()->has(HeaderName::UserAgent));
		self::assertTrue($request->getHeaders()->anyKey(fn(\Elephox\Http\Contract\HeaderName $header) => $header->getValue() === "X-Custom"));
		self::assertEquals("application/json", $request->getHeaders()->get(HeaderName::Accept));
		self::assertEquals("test/1.0.0", $request->getHeaders()->get(HeaderName::UserAgent));
		self::assertEquals(["custom-test"], $request->getHeaders()->get("X-Custom"));

		unset($_SERVER['REQUEST_METHOD']);
		unset($_SERVER['REQUEST_URI']);
		unset($_SERVER['HTTP_ACCEPT']);
		unset($_SERVER['HTTP_USER_AGENT']);
		unset($_SERVER['HTTP_X_CUSTOM']);
	}

	public function testFromGlobalsCustomRequestMethod(): void
	{
		$_SERVER['REQUEST_METHOD'] = "NEW";
		$_SERVER['REQUEST_URI'] = "/";

		$request = Request::fromGlobals();

		self::assertInstanceOf(CustomRequestMethod::class, $request->getMethod());
		self::assertEquals("NEW", $request->getMethod()->getValue());
		self::assertTrue($request->getMethod()->canHaveBody());
		self::assertEquals('/', $request->getUrl()->__toString());

		unset($_SERVER['REQUEST_METHOD']);
		unset($_SERVER['REQUEST_URI']);
	}

	public function testFromGlobalsInvalidRequestMethod(): void
	{
		$this->expectException(Exception::class);

		Request::fromGlobals();
	}

	public function testFromGlobalsInvalidRequestUri(): void
	{
		$_SERVER['REQUEST_METHOD'] = "GET";

		$this->expectException(Exception::class);

		Request::fromGlobals();

		unset($_SERVER['REQUEST_METHOD']);
	}

	public function testInvalidRequestMethodBody(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new Request(RequestMethod::GET, "/", body: "test");
	}

	public function testGetJson(): void
	{
		$request = new Request("POST", "/", body: '{"test": "test"}');

		self::assertEquals(["test" => "test"], $request->getJson());
	}

	public function testGetJsonEmptyBody(): void
	{
		$request = new Request("POST", "/");

		self::assertEquals([], $request->getJson());
	}

	public function testGetJsonWithContentTypeHeader(): void
	{
		$request = new Request("POST", "/", ['Content-Type' => "application/json"], '{"test": "test"}');

		self::assertEquals(["test" => "test"], $request->getJson());
	}

	public function testGetJsonWithInvalidContentTypeHeader(): void
	{
		$this->expectException(LogicException::class);

		$request = new Request("POST", "/", ['Content-Type' => "text/xml"], '{"test": "test"}');
		$request->getJson();
	}

	public function testGetJsonWithInvalidRequestMethod(): void
	{
		$this->expectException(LogicException::class);

		$request = new Request("GET", "/");
		$request->getJson();
	}

	public function testRequestCannotContainResponseOnlyHeaders(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new Request("GET", "/", ['Server' => "test"]);
	}

	public function testRequestBodyGetsRead(): void
	{
		$_SERVER['REQUEST_METHOD'] = "POST";
		$_SERVER['REQUEST_URI'] = "/";

		$request = Request::fromGlobals();

		self::assertNull($request->getBody());

		unset($_SERVER['REQUEST_METHOD']);
		unset($_SERVER['REQUEST_URI']);
	}

	public function testRequestBodyGetsReadWithContentLength(): void
	{
		$_SERVER['REQUEST_METHOD'] = "POST";
		$_SERVER['REQUEST_URI'] = "/";
		$_SERVER['CONTENT_LENGTH'] = "1";
		$_SERVER['CONTENT_TYPE'] = "text/plain";

		$request = Request::fromGlobals();

		self::assertEquals("", $request->getBody());

		unset($_SERVER['REQUEST_METHOD']);
		unset($_SERVER['REQUEST_URI']);
		unset($_SERVER['CONTENT_LENGTH']);
		unset($_SERVER['CONTENT_TYPE']);
	}
}
