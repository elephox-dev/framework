<?php
declare(strict_types=1);

namespace Elephox\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\Request
 * @covers \Elephox\Collection\GenericWeakMap
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Http\RequestMethod
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Http\UrlScheme
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\RequestHeaderMap
 * @covers \Elephox\Http\HeaderName
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
		$headers->put(HeaderName::Server, ["test"]);

		$request = new Request("GET", "/test", $headers);

		self::assertEquals(RequestMethod::GET, $request->getMethod());
		self::assertEquals('/test', $request->getUrl()->getPath());
		self::assertCount(1, $request->getHeaders()->asArray());
		self::assertEquals("test", $request->getHeaders()->get(HeaderName::Server)[0]);
	}
}
