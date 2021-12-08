<?php
declare(strict_types=1);

namespace Elephox\Http;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

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
 * @covers \Elephox\Http\LazyResourceStream
 * @covers \Elephox\Http\ResourceStream
 * @covers \Elephox\Http\StringStream
 * @covers \Elephox\Http\EmptyStream
 * @covers \Elephox\Collection\ArrayList
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
		self::assertInstanceOf(StreamInterface::class, $r->getBody());
		self::assertSame('baz', (string)$r->getBody());
	}

	public function testNullBody(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('/'));
		self::assertInstanceOf(StreamInterface::class, $r->getBody());
		self::assertSame('', (string)$r->getBody());
	}

	public function testFalseyBody(): void
	{
		$r = new Request(RequestMethod::POST, Url::fromString('/'), body: new StringStream('0'));
		self::assertInstanceOf(StreamInterface::class, $r->getBody());
		self::assertSame('0', (string)$r->getBody());
	}

	public function testCapitalizesWithMethod(): void
	{
		$r = new Request(RequestMethod::PUT, Url::fromString('/'));
		self::assertSame('PUT', $r->withMethod('put')->getMethod());
	}

	public function testWithUri(): void
	{
		$r1 = new Request(RequestMethod::GET, Url::fromString('/'));
		$u1 = $r1->getUri();
		$u2 = Url::fromString('http://www.example.com');
		$r2 = $r1->withUri($u2);
		self::assertNotSame($r1, $r2);
		self::assertSame($u2, $r2->getUri());
		self::assertSame($u1, $r1->getUri());
	}

	/**
	 * @dataProvider invalidMethodsProvider
	 */
	public function testConstructWithInvalidMethods($method): void
	{
		$this->expectException(\TypeError::class);
		new Request($method, '/');
	}

	/**
	 * @dataProvider invalidMethodsProvider
	 */
	public function testWithInvalidMethods($method): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('/'));
		$this->expectException(InvalidArgumentException::class);
		$r->withMethod($method);
	}

	public function invalidMethodsProvider(): iterable
	{
		return [
			[null],
			[false],
			[0],
		];
	}

	public function testSameInstanceWhenSameUri(): void
	{
		$r1 = new Request(RequestMethod::GET, Url::fromString('http://foo.com'));
		$r2 = $r1->withUri($r1->getUri());
		self::assertSame($r1, $r2);
	}

	public function testWithRequestTarget(): void
	{
		$r1 = new Request(RequestMethod::GET, Url::fromString('/'));
		$r2 = $r1->withRequestTarget('/test');
		self::assertSame('/test', $r2->getRequestTarget());
		self::assertSame('/', $r1->getRequestTarget());
	}

	public function testRequestTargetDoesNotAllowSpaces(): void
	{
		$r1 = new Request(RequestMethod::GET, Url::fromString('/'));
		$this->expectException(InvalidArgumentException::class);
		$r1->withRequestTarget('/foo bar');
	}

	public function testRequestTargetDefaultsToSlash(): void
	{
		$r1 = new Request(RequestMethod::GET, Url::fromString(''));
		self::assertSame('/', $r1->getRequestTarget());
		$r2 = new Request(RequestMethod::GET, Url::fromString('*'));
		self::assertSame('*', $r2->getRequestTarget());
		$r3 = new Request(RequestMethod::GET, Url::fromString('http://foo.com/bar baz/'));
		self::assertSame('/bar%20baz/', $r3->getRequestTarget());
	}

	public function testBuildsRequestTarget(): void
	{
		$r1 = new Request(RequestMethod::GET, Url::fromString('http://foo.com/baz?bar=bam'));
		self::assertSame('/baz?bar=bam', $r1->getRequestTarget());
	}

	public function testBuildsRequestTargetWithFalseyQuery(): void
	{
		$r1 = new Request(RequestMethod::GET, Url::fromString('http://foo.com/baz?0'));
		self::assertSame('/baz?0', $r1->getRequestTarget());
	}

	public function testHostIsAddedFirst(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('http://foo.com/baz?bar=bam'), RequestHeaderMap::fromArray(['Foo' => 'Bar']));
		self::assertSame([
			'Host' => ['foo.com'],
			'Foo' => ['Bar']
		], $r->getHeaders());
	}

	public function testCanGetHeaderAsCsv(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('http://foo.com/baz?bar=bam'), RequestHeaderMap::fromArray([
			'Foo' => ['a', 'b', 'c']
		]));
		self::assertSame('a, b, c', $r->getHeaderLine('Foo'));
		self::assertSame('', $r->getHeaderLine('Bar'));
	}

	/**
	 * @dataProvider provideHeadersContainingNotAllowedChars
	 */
	public function testContainsNotAllowedCharsOnHeaderField($header): void
	{
		$this->expectExceptionMessage(
			sprintf(
				'Invalid header name: %s',
				$header
			)
		);
		$r = new Request(
			RequestMethod::GET,
			Url::fromString('http://foo.com/baz?bar=bam'),
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
	public function testContainsAllowedCharsOnHeaderField($header): void
	{
		$r = new Request(
			RequestMethod::GET,
			Url::fromString('http://foo.com/baz?bar=bam'),
			RequestHeaderMap::fromArray([
				$header => 'value'
			]),
		);
		self::assertArrayHasKey($header, $r->getHeaders());
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
		$r = new Request(RequestMethod::GET, Url::fromString('http://foo.com/baz?bar=bam'), RequestHeaderMap::fromArray(['Host' => 'a.com']));
		self::assertSame(['Host' => ['a.com']], $r->getHeaders());
		$r2 = $r->withUri(Url::fromString('http://www.foo.com/bar'), true);
		self::assertSame('a.com', $r2->getHeaderLine('Host'));
	}

	public function testWithUriSetsHostIfNotSet(): void
	{
		$r = (new Request(RequestMethod::GET, Url::fromString('http://foo.com/baz?bar=bam')))->withoutHeader('Host');
		self::assertSame([], $r->getHeaders());
		$r2 = $r->withUri(Url::fromString('http://www.baz.com/bar'), true);
		self::assertSame('www.baz.com', $r2->getHeaderLine('Host'));
	}

	public function testOverridesHostWithUri(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('http://foo.com/baz?bar=bam'));
		self::assertSame(['Host' => ['foo.com']], $r->getHeaders());
		$r2 = $r->withUri(Url::fromString('http://www.baz.com/bar'));
		self::assertSame('www.baz.com', $r2->getHeaderLine('Host'));
	}

	public function testAggregatesHeaders(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString(''), RequestHeaderMap::fromArray([
			'ZOO' => 'zoobar',
			'zoo' => ['foobar', 'zoobar']
		]));
		self::assertSame(['ZOO' => ['zoobar', 'foobar', 'zoobar']], $r->getHeaders());
		self::assertSame('zoobar, foobar, zoobar', $r->getHeaderLine('zoo'));
	}

	public function testAddsPortToHeader(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('http://foo.com:8124/bar'));
		self::assertSame('foo.com:8124', $r->getHeaderLine('host'));
	}

	public function testAddsPortToHeaderAndReplacePreviousPort(): void
	{
		$r = new Request(RequestMethod::GET, Url::fromString('http://foo.com:8124/bar'));
		$r = $r->withUri(Url::fromString('http://foo.com:8125/bar'));
		self::assertSame('foo.com:8125', $r->getHeaderLine('host'));
	}
}
