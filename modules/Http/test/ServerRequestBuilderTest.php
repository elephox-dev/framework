<?php
declare(strict_types=1);

namespace Elephox\Http;

use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\ServerRequestBuilder
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\ObjectMap
 * @covers \Elephox\Http\AbstractMessage
 * @covers \Elephox\Http\AbstractMessageBuilder
 * @covers \Elephox\Http\CookieMap
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Http\ParameterMap
 * @covers \Elephox\Http\Request
 * @covers \Elephox\Http\RequestBuilder
 * @covers \Elephox\Http\ServerRequest
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Http\UrlScheme
 * @covers \Elephox\Http\UrlBuilder
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Http\UploadedFileMap
 * @covers \Elephox\Http\RequestMethod
 * @covers \Elephox\Http\AbstractBuilder
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Stream\EmptyStream
 * @covers \Elephox\Http\SessionMap
 * @covers \Elephox\Platform\Session
 *
 * @uses \Elephox\Http\Contract\Request
 * @uses \Elephox\Http\Contract\ServerRequest
 *
 * @internal
 */
class ServerRequestBuilderTest extends TestCase
{
	public function globalsProvider(): iterable
	{
		yield [ParameterMap::fromGlobals([], [], ['REQUEST_URI' => '/test'], []), '/test', null, null, AbstractMessageBuilder::DefaultProtocolVersion, null, RequestMethod::GET];
		yield [ParameterMap::fromGlobals([], [], ['REQUEST_URI' => '/foo'], ['REQUEST_URI' => '/bar']), '/foo', null, null, AbstractMessageBuilder::DefaultProtocolVersion, null, RequestMethod::GET];
		yield [ParameterMap::fromGlobals([], [], ['REQUEST_URI' => '/test', 'CONTENT_LENGTH' => 0], []), '/test', null, null, AbstractMessageBuilder::DefaultProtocolVersion, null, RequestMethod::GET];
		yield [ParameterMap::fromGlobals([], [], ['REQUEST_URI' => '/test', 'CONTENT_LENGTH' => 1], []), '/test', 1, null, AbstractMessageBuilder::DefaultProtocolVersion, null, RequestMethod::GET];
		yield [ParameterMap::fromGlobals([], [], ['REQUEST_URI' => '/test', 'SERVER_PROTOCOL' => 'HTTP/2.0'], []), '/test', null, '2.1', '2.1', null, RequestMethod::GET];
		yield [ParameterMap::fromGlobals([], [], ['REQUEST_URI' => '/test', 'SERVER_PROTOCOL' => 'HTTP/2.0'], []), '/test', null, null, '2.0', null, RequestMethod::GET];
		yield [ParameterMap::fromGlobals([], [], ['REQUEST_URI' => '/test', 'REQUEST_METHOD' => 'POST'], []), '/test', null, null, AbstractMessageBuilder::DefaultProtocolVersion, RequestMethod::GET, RequestMethod::GET];
		yield [ParameterMap::fromGlobals([], [], ['REQUEST_URI' => '/test', 'REQUEST_METHOD' => 'POST'], []), '/test', null, null, AbstractMessageBuilder::DefaultProtocolVersion, null, RequestMethod::POST];
	}

	/**
	 * @dataProvider globalsProvider
	 *
	 * @param ParameterMap $parameterMap
	 * @param string $requestUri
	 * @param ?int $bodyStreamLength
	 * @param ?string $protocolVersion
	 * @param string $expectedProtocolVersion
	 * @param ?RequestMethod $requestMethod
	 * @param RequestMethod $expectedRequestMethod
	 */
	public function testFromGlobals(ParameterMap $parameterMap, string $requestUri, ?int $bodyStreamLength, ?string $protocolVersion, string $expectedProtocolVersion, ?RequestMethod $requestMethod, RequestMethod $expectedRequestMethod): void
	{
		$request = ServerRequestBuilder::fromGlobals($parameterMap, protocolVersion: $protocolVersion, requestMethod: $requestMethod);

		static::assertEquals($requestUri, $request->getUrl()->path);
		static::assertEquals($bodyStreamLength, $request->getBody()->getSize());
		static::assertEquals($expectedProtocolVersion, $request->getProtocolVersion());
		static::assertEquals($expectedRequestMethod, $request->getMethod());
	}

	public function testMissingParameterIsThrown(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Missing required parameter: url');

		ServerRequestBuilder::fromGlobals(new ParameterMap(), session: new FakeSessionMap());
	}

	public function testGetters(): void
	{
		$builder = ServerRequest::build();
		$builder->requestMethod(RequestMethod::GET);
		$builder->requestUrl(Url::fromString('http://example.com/foo'));

		static::assertEquals(RequestMethod::GET, $builder->getRequestMethod());
		static::assertEquals('http://example.com/foo', (string) $builder->getRequestUrl());
	}

	public function testFromRequest(): void
	{
		$request = Request::build()
			->protocolVersion('2.0')
			->header('header', 'value')
			->addHeader('header', 'another')
			->htmlBody('html')
			->requestMethod(RequestMethod::GET)
			->requestUrl(Url::fromString('http://example.com/foo'))
			->get()
		;

		$serverRequestBuilder = ServerRequestBuilder::fromRequest($request);

		static::assertEquals('2.0', $serverRequestBuilder->getProtocolVersion());
		static::assertEquals(['value', 'another'], $serverRequestBuilder->getHeaderMap()?->get('header'));
		static::assertEquals('html', $serverRequestBuilder->getBody()?->getContents());
		static::assertEquals(RequestMethod::GET, $serverRequestBuilder->getRequestMethod());
	}
}
