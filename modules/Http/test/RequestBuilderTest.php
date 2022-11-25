<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\Request as RequestContract;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use JsonException;

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
 * @covers \Elephox\OOR\Casing
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Http\HeaderMap
 *
 * @uses \Elephox\Http\Contract\Request
 *
 * @internal
 */
class RequestBuilderTest extends TestCase
{
	/**
	 * @throws JsonException
	 */
	public function testBuild(): void
	{
		$builder = Request::build();
		$builder->requestMethod(RequestMethod::GET);
		$builder->requestUrl(Url::fromString('https://example.com/'));
		$builder->protocolVersion('2.0');
		$builder->jsonBody(['foo' => 'bar']);
		$builder->header('X-Foo', 'bar');
		$builder->header('X-Bar', 'baz');

		$request = $builder->get();
		static::assertInstanceOf(RequestContract::class, $request);
		static::assertSame(RequestMethod::GET, $request->getRequestMethod());
		static::assertSame('https://example.com/', (string) $request->getUrl());
		static::assertSame('2.0', $request->getProtocolVersion());
		static::assertSame('{"foo":"bar"}', $request->getBody()->getContents());
		static::assertSame(['bar'], $request->getHeaderMap()->get('X-Foo'));
		static::assertSame(['baz'], $request->getHeaderMap()->get('X-Bar'));

		$newRequest = $request->with()->jsonBody(['foo2' => 'bar2'])->get();
		static::assertSame(RequestMethod::GET, $request->getRequestMethod());
		static::assertSame('https://example.com/', (string) $request->getUrl());
		static::assertSame('2.0', $request->getProtocolVersion());
		static::assertSame('{"foo2":"bar2"}', $newRequest->getBody()->getContents());
		static::assertSame(['bar'], $request->getHeaderMap()->get('X-Foo'));
		static::assertSame(['baz'], $request->getHeaderMap()->get('X-Bar'));
	}

	public function invalidBodyResourceProvider(): iterable
	{
		yield [null];
		yield [false];
		yield [true];
		yield [0];
		yield [1];
		yield [1.1];
		yield [''];
		yield ['foo'];
		yield [[]];
		yield [['foo', 'bar']];
	}

	/**
	 * @dataProvider invalidBodyResourceProvider
	 *
	 * @param mixed $body
	 */
	public function testInvalidResourceBody(mixed $body): void
	{
		$this->expectException(InvalidArgumentException::class);

		Request::build()->resourceBody($body);
	}
}
