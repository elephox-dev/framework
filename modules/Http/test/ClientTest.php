<?php
declare(strict_types=1);

namespace Elephox\Http;

use Mockery as M;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Elephox\Http\Contract\HttpAdapter;

/**
 * @covers \Elephox\Http\Client
 * @covers \Elephox\Http\ResponseCode
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\GenericWeakMap
 * @covers \Elephox\Collection\KeyValuePair
 * @covers \Elephox\Http\Request
 * @covers \Elephox\Http\Response
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Text\Regex
 * @covers \Elephox\Http\RequestMethod
 * @covers \Elephox\Http\RequestHeaderMap
 * @covers \Elephox\Http\ResponseHeaderMap
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Http\HeaderName
 */
class ClientTest extends MockeryTestCase
{
	public function testExecute(): void
	{
		$httpAdapterMock = M::mock(HttpAdapter::class);

		$requestMethod = 'GET';
		$requestUrl = 'https://example.com/';
		$responseText = <<<MESSAGE
HTTP/1.1 200 OK
Date: Mon, 27 Jul 2009 12:28:53 GMT
Server: Apache/2.2.14 (Win32)
Last-Modified: Wed, 22 Jul 2009 19:15:56 GMT
Content-Length: 88
Content-Type: application/json
Connection: Closed

<html>
<body>
<h1>Hello, World!</h1>
</body>
</html>
MESSAGE;

		$httpAdapterMock
			->expects('prepare')
			->once()
			->withNoArgs()
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('setHeaders')
			->once()
			->with([])
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('setBody')
			->once()
			->with(null)
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('setMethod')
			->once()
			->with($requestMethod)
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('setUrl')
			->once()
			->with($requestUrl)
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('send')
			->once()
			->withNoArgs()
			->andReturn(true)
		;
		$httpAdapterMock
			->expects('getResponse')
			->once()
			->withNoArgs()
			->andReturn($responseText)
		;
		$httpAdapterMock
			->expects('cleanup')
			->once()
			->withNoArgs()
		;

		$client = new Client($httpAdapterMock);
		$request = new Request($requestMethod, $requestUrl);
		$response = $client->execute($request);

		self::assertInstanceOf(Response::class, $response);
	}
}
