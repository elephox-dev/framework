<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\Request as RequestContract;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\RequestBuilder
 * @covers \Elephox\Http\Request
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Http\UrlBuilder
 * @covers \Elephox\Http\AbstractMessage
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\AbstractMessageBuilder
 * @covers \Elephox\Http\RequestMethod
 * @covers \Elephox\Http\UrlScheme
 * @covers \Elephox\Stream\StringStream
 * @uses \Elephox\Http\Contract\Request
 */
class RequestBuilderTest extends TestCase
{
	public function testBuild(): void
	{
		$builder = Request::build();
		$builder->requestMethod(RequestMethod::GET);
		$builder->requestUrl(Url::fromString('https://example.com/'));
		$builder->protocolVersion("2.0");
		$builder->jsonBody(['foo' => 'bar']);
		$builder->header('X-Foo', ['bar']);
		$builder->header('X-Bar', ['baz']);

		$request = $builder->get();
		self::assertInstanceOf(RequestContract::class, $request);
		self::assertEquals(RequestMethod::GET, $request->getMethod());
		self::assertEquals('https://example.com/', (string)$request->getUrl());
		self::assertEquals('2.0', $request->getProtocolVersion());
		self::assertEquals('{"foo":"bar"}', $request->getBody()->getContents());
		self::assertEquals(['bar'], $request->getHeaderMap()->get('X-Foo'));
		self::assertEquals(['baz'], $request->getHeaderMap()->get('X-Bar'));

		$newRequest = $request->with()->jsonBody(['foo2' => 'bar2'])->get();
		self::assertEquals(RequestMethod::GET, $request->getMethod());
		self::assertEquals('https://example.com/', (string)$request->getUrl());
		self::assertEquals('2.0', $request->getProtocolVersion());
		self::assertEquals('{"foo2":"bar2"}', $newRequest->getBody()->getContents());
		self::assertEquals(['bar'], $request->getHeaderMap()->get('X-Foo'));
		self::assertEquals(['baz'], $request->getHeaderMap()->get('X-Bar'));
	}
}
