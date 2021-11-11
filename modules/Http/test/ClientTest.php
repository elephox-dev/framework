<?php
declare(strict_types=1);

namespace Philly\Http;

use Mockery as M;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Philly\Http\Contract\HttpAdapter;

/**
 * @covers \Philly\Http\Client
 * @covers \Philly\Http\ResponseCode
 * @covers \Philly\Collection\ArrayList
 * @covers \Philly\Collection\ArrayMap
 * @covers \Philly\Collection\GenericWeakMap
 * @covers \Philly\Collection\KeyValuePair
 * @covers \Philly\Http\Request
 * @covers \Philly\Http\Response
 * @covers \Philly\Http\Url
 * @covers \Philly\Text\Regex
 * @covers \Philly\Http\RequestMethod
 * @covers \Philly\Http\RequestHeaderMap
 * @covers \Philly\Http\ResponseHeaderMap
 * @covers \Philly\Http\HeaderMap
 * @covers \Philly\Http\HeaderName
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
