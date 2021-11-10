<?php
declare(strict_types=1);

namespace Philly\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Philly\Http\Request
 * @covers \Philly\Collection\GenericWeakMap
 * @covers \Philly\Http\HeaderMap
 * @covers \Philly\Http\RequestMethod
 * @covers \Philly\Http\Url
 * @covers \Philly\Http\UrlScheme
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
