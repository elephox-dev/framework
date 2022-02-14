<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Collection\ArrayMap;
use Elephox\Http\Contract\SessionMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\ServerRequestBuilder
 */
class ServerRequestBuilderTest extends TestCase
{
	public function parameterMapProvider(): iterable
	{
		yield [ ParameterMap::fromGlobals([], [], ['REQUEST_URI' => '/test'], []), '/test' ];
		yield [ ParameterMap::fromGlobals([], [], ['REQUEST_URI' => '/foo'], ['REQUEST_URI' => '/bar']), '/foo' ];
	}

	/**
	 * @dataProvider parameterMapProvider
	 */
	public function testDefaultParametersComeFromServer(ParameterMap $parameterMap, string $requestUri): void
	{
		$request = ServerRequestBuilder::fromGlobals($parameterMap, session: new FakeSessionMap());

		self::assertEquals($requestUri, $request->getUrl()->path);
	}
}

class FakeSessionMap extends ArrayMap implements SessionMap
{
	public static function fromGlobals(?array $session = null, bool $recreate = false): ?SessionMap
	{
		return new self($session ?? []);
	}
}
