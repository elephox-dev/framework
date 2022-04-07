<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Http\Contract\SessionMap;
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
 * @covers \Elephox\Http\UrlBuilder
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Http\UploadedFileMap
 * @covers \Elephox\Http\RequestMethod
 * @covers \Elephox\Http\AbstractBuilder
 *
 * @uses \Elephox\Http\Contract\Request
 * @uses \Elephox\Http\Contract\ServerRequest
 *
 * @internal
 */
class ServerRequestBuilderTest extends TestCase
{
	public function parameterMapProvider(): iterable
	{
		yield [ParameterMap::fromGlobals([], [], ['REQUEST_URI' => '/test'], []), '/test'];
		yield [ParameterMap::fromGlobals([], [], ['REQUEST_URI' => '/foo'], ['REQUEST_URI' => '/bar']), '/foo'];
	}

	/**
	 * @dataProvider parameterMapProvider
	 */
	public function testDefaultParametersComeFromServer(ParameterMap $parameterMap, string $requestUri): void
	{
		$request = ServerRequestBuilder::fromGlobals($parameterMap, session: new FakeSessionMap());

		static::assertEquals($requestUri, $request->getUrl()->path);
	}

	public function testMissingParameterIsThrown(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Missing required parameter: url');

		ServerRequestBuilder::fromGlobals(new ParameterMap(), session: new FakeSessionMap());
	}
}

class FakeSessionMap extends ArrayMap implements SessionMap
{
	public static function fromGlobals(?array $session = null, bool $recreate = false): ?SessionMap
	{
		return new self($session ?? []);
	}
}
