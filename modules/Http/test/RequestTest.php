<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Stream\Contract\Stream;
use Elephox\Stream\EmptyStream;
use Elephox\Stream\StringStream;
use Exception;
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
 * @covers \Elephox\Http\AbstractMessage
 * @covers \Elephox\Stream\LazyStream
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Stream\EmptyStream
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Http\InvalidHeaderNameException
 * @uses \Elephox\Http\Contract\Url
 */
class RequestTest extends TestCase
{
	public function testConstructorStrings(): void
	{
		$request = new Request(RequestMethod::GET, Url::fromString("/test"), new RequestHeaderMap(), new EmptyStream(), "1.1");

		self::assertEquals(RequestMethod::GET, $request->getRequestMethod());
		self::assertEquals('/test', $request->getUri()->getPath());
		self::assertCount(0, $request->getHeaderMap()->asArray());
	}

	public function testConstructorMethodObject(): void
	{
		$request = new Request(RequestMethod::POST, Url::fromString("/test"), new RequestHeaderMap(), new EmptyStream(), "1.1");

		self::assertEquals(RequestMethod::POST, $request->getRequestMethod());
		self::assertEquals('/test', $request->getUri()->getPath());
		self::assertCount(0, $request->getHeaderMap()->asArray());
	}

	public function testConstructorUrlObject(): void
	{
		$request = new Request(RequestMethod::DELETE, Url::fromString("/test"), new RequestHeaderMap(), new EmptyStream(), "1.1");

		self::assertEquals(RequestMethod::DELETE, $request->getRequestMethod());
		self::assertEquals('/test', $request->getUri()->getPath());
		self::assertCount(0, $request->getHeaderMap()->asArray());
	}

	public function testConstructorHeaderMap(): void
	{
		$headers = new RequestHeaderMap();
		$headers->put(HeaderName::Host, ["test"]);

		$request = new Request(RequestMethod::GET, Url::fromString("/test"), $headers, new EmptyStream(), "1.1");

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
		self::assertEquals('/', (string)$request->getUrl());
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
		self::assertEquals('/', (string)$request->getUrl());

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

	public function testRequestUriMayBeString(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('/'));
		self::assertSame('/', (string)$r->getUri());
	}

	public function testRequestUriMayBeUri(): void
	{
		$uri = Url::fromString('/');
		$r = new Request(RequestMethod::GET, $uri);
		self::assertSame($uri, $r->getUri());
	}

	public function testCanConstructWithBody(): void
	{
		$r = new Request(RequestMethod::POST, Url::fromString('/'), body: new StringStream('baz'));
		self::assertInstanceOf(Stream::class, $r->getBody());
		self::assertSame('baz', (string)$r->getBody());
	}

	public function testNullBody(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('/'));
		self::assertInstanceOf(Stream::class, $r->getBody());
		self::assertSame('', (string)$r->getBody());
	}

	public function testFalseyBody(): void
	{
		$r = new Request(RequestMethod::POST, Url::fromString('/'), body: new StringStream('0'));
		self::assertInstanceOf(Stream::class, $r->getBody());
		self::assertSame('0', (string)$r->getBody());
	}

	public function testWithUri(): void
	{
		$r1 = new Request(RequestMethod::GET, Url::fromString('/'));
		$u1 = $r1->getUri();
		$u2 = Url::fromString('https://www.example.com');
		$r2 = $r1->withUrl($u2);
		self::assertNotSame($r1, $r2);
		self::assertSame($u2, $r2->getUri());
		self::assertSame($u1, $r1->getUri());
	}

	public function testSameInstanceWhenSameUri(): void
	{
		$r1 = new Request(RequestMethod::GET, Url::fromString('https://foo.com'));
		$r2 = $r1->withUrl($r1->getUrl());
		self::assertSame($r1, $r2);
	}

	public function testHostIsAddedFirst(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('https://foo.com/baz?bar=bam'), RequestHeaderMap::fromArray(['Foo' => 'Bar']));
		self::assertSame([
			'Host' => ['foo.com'],
			'Foo' => ['Bar']
		], $r->getHeaderMap()->asArray());
	}

	/**
	 * @dataProvider provideHeadersContainingNotAllowedChars
	 */
	public function testContainsNotAllowedCharsOnHeaderField(string $header): void
	{
		$this->expectExceptionMessage(
			sprintf(
				'Invalid header name: %s',
				$header
			)
		);
		new Request(
			RequestMethod::GET,
			Url::fromString('https://foo.com/baz?bar=bam'),
			RequestHeaderMap::fromArray([
				$header => 'value'
			]),
		);
	}

	public function provideHeadersContainingNotAllowedChars(): iterable
	{
		return [[' key '], ['key '], [' key'], ['key/'], ['key('], ['key\\'], [' ']];
	}

	/**
	 * @dataProvider provideHeadersContainsAllowedChar
	 */
	public function testContainsAllowedCharsOnHeaderField(string $header): void
	{
		$r = new Request(
			RequestMethod::GET,
			Url::fromString('https://foo.com/baz?bar=bam'),
			RequestHeaderMap::fromArray([
				$header => 'value'
			]),
		);
		self::assertArrayHasKey($header, $r->getHeaderMap()->asArray());
	}

	public function provideHeadersContainsAllowedChar(): iterable
	{
		return [
			['key'],
			['key#'],
			['key$'],
			['key%'],
			['key&'],
			['key*'],
			['key+'],
			['key.'],
			['key^'],
			['key_'],
			['key|'],
			['key~'],
			['key!'],
			['key-'],
			["key'"],
			['key`']
		];
	}

	public function testHostIsNotOverwrittenWhenPreservingHost(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('https://foo.com/baz?bar=bam'), RequestHeaderMap::fromArray(['Host' => 'a.com']));
		self::assertSame(['Host' => ['a.com']], $r->getHeaderMap()->asArray());
		$r2 = $r->withUrl(Url::fromString('https://www.foo.com/bar'), true);
		self::assertSame('a.com', $r2->getHeaderMap()->get(HeaderName::Host)->first());
	}

	public function testWithUriSetsHostIfNotSet(): void
	{
		$r = (new Request(RequestMethod::GET, Url::fromString('https://foo.com/baz?bar=bam')))->withoutHeaderName(HeaderName::Host);
		self::assertSame([], $r->getHeaderMap()->asArray());
		$r2 = $r->withUrl(Url::fromString('https://www.baz.com/bar'), true);
		self::assertSame('www.baz.com', $r2->getHeaderMap()->get(HeaderName::Host)->first());
	}

	public function testOverridesHostWithUri(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('https://foo.com/baz?bar=bam'));
		self::assertSame(['Host' => ['foo.com']], $r->getHeaderMap()->asArray());
		$r2 = $r->withUrl(Url::fromString('https://www.baz.com/bar'));
		self::assertSame('www.baz.com', $r2->getHeaderMap()->get(HeaderName::Host)->first());
	}

	public function testAggregatesHeaders(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString(''), RequestHeaderMap::fromArray([
			'ZOO' => 'zoobar',
			'zoo' => ['foobar', 'zoobar']
		]));
		self::assertSame(['ZOO' => ['zoobar', 'foobar', 'zoobar']], $r->getHeaderMap()->asArray());
		self::assertSame('zoobar, foobar, zoobar', $r->getHeaderMap()->get("ZOO")->join(', '));
	}

	public function testAddsPortToHeader(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('https://foo.com:8124/bar'));
		self::assertSame('foo.com:8124', $r->getHeaderMap()->get(HeaderName::Host)->first());
	}

	public function testAddsPortToHeaderAndReplacePreviousPort(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('https://foo.com:8124/bar'));
		$r = $r->withUrl(Url::fromString('https://foo.com:8125/bar'));
		self::assertSame('foo.com:8125', $r->getHeaderMap()->get(HeaderName::Host)->first());
	}
}
