<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Files\File;
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
 * @covers \Elephox\Collection\IteratorProvider
 * @covers \Elephox\Collection\Iterator\SelectIterator
 * @covers \Elephox\Files\AbstractFilesystemNode
 * @covers \Elephox\Files\File
 * @covers \Elephox\Http\SessionMap
 * @covers \Elephox\OOR\Casing
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Http\HeaderName
 *
 * @internal
 */
final class ServerRequestTest extends TestCase
{
	public function testGetters(): void
	{
		$request = ServerRequest::build()->requestUrl(Url::fromString('https://example.com/foo'))->session(new FakeSessionMap())->get();

		self::assertInstanceOf(ServerRequest::class, $request);

		$builder = $request->with();
		$builder->parameter('foo', 'bar', ParameterSource::Post);
		$builder->cookie(new Cookie('cookie', 'value'));
		$builder->uploadedFile('file', new UploadedFile('/tmp/file', 'file', new File('path')));
		$builder->sessionParam('session', 'value');

		$builderCookies = $builder->getCookies();
		$builderParameters = $builder->getParameters();
		$builderUploadedFiles = $builder->getUploadedFiles();
		$builderSession = $builder->getSession();

		$newRequest = $builder->get();

		$cookies = $newRequest->getCookieMap();
		$parameters = $newRequest->getParameterMap();
		$files = $newRequest->getUploadedFileMap();
		$session = $newRequest->getSessionMap();

		self::assertSame($builderCookies, $cookies);
		self::assertSame($builderParameters, $parameters);
		self::assertSame($builderUploadedFiles, $files);
		self::assertSame($builderSession, $session);

		self::assertCount(1, $cookies);
		self::assertCount(1, $files);

		self::assertTrue($cookies->has('cookie'));
		self::assertSame('value', $cookies->get('cookie')->getValue());
		self::assertSame('bar', $parameters->get('foo'));
		self::assertSame('value', $session?->get('session'));
	}
}
