<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Stream\StringStream;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\ObjectMap
 * @covers \Elephox\Http\AbstractMessage
 * @covers \Elephox\Http\AbstractMessageBuilder
 * @covers \Elephox\Http\ParameterMap
 * @covers \Elephox\Http\Request
 * @covers \Elephox\Http\RequestBuilder
 * @covers \Elephox\Http\ServerRequest
 * @covers \Elephox\Http\ServerRequestBuilder
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Http\UrlBuilder
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Http\Cookie
 * @covers \Elephox\Http\UploadedFile
 *
 * @internal
 */
class ServerRequestTest extends TestCase
{
	public function testGetters(): void
	{
		$request = ServerRequest::build()->requestUrl(Url::fromString('https://example.com/foo'))->session(new FakeSessionMap())->get();

		static::assertInstanceOf(ServerRequest::class, $request);

		$builder = $request->with();
		$builder->parameter('foo', 'bar', ParameterSource::Post);
		$builder->cookie(new Cookie('cookie', 'value'));
		$builder->uploadedFile('file', new UploadedFile('/tmp/file', 'file', new StringStream('contents')));
		$builder->sessionParam('session', 'value');

		$builderCookies = $builder->getCookies();
		$builderParameters = $builder->getParameters();
		$builderUploadedFiles = $builder->getUploadedFiles();
		$builderSession = $builder->getSession();

		$newRequest = $builder->get();

		$cookies = $newRequest->getCookies();
		$parameters = $newRequest->getParameters();
		$files = $newRequest->getUploadedFiles();
		$session = $newRequest->getSession();

		static::assertSame($builderCookies, $cookies);
		static::assertSame($builderParameters, $parameters);
		static::assertSame($builderUploadedFiles, $files);
		static::assertSame($builderSession, $session);

		static::assertCount(1, $cookies);
		static::assertCount(1, $files);

		static::assertTrue($cookies->has('cookie'));
		static::assertEquals('value', $cookies->get('cookie')->getValue());
		static::assertEquals('bar', $parameters->get('foo'));
		static::assertEquals('value', $session?->get('session'));
	}
}
