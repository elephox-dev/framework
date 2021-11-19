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
 * @covers \Elephox\Collection\ObjectMap
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
 * @covers \Elephox\Http\ClientException
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
			->withNoArgs()
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('setHeaders')
			->with(['Host: localhost'])
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('setBody')
			->with(null)
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('setMethod')
			->with($requestMethod)
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('setUrl')
			->with($requestUrl)
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('send')
			->withNoArgs()
			->andReturn(true)
		;
		$httpAdapterMock
			->expects('getResponse')
			->withNoArgs()
			->andReturn($responseText)
		;
		$httpAdapterMock
			->expects('cleanup')
			->withNoArgs()
		;

		$client = new Client($httpAdapterMock);
		$request = new Request($requestMethod, $requestUrl, ['Host' => 'localhost']);
		$response = $client->execute($request);

		self::assertInstanceOf(Response::class, $response);
	}

	public function testSendFailure(): void
	{
		$httpAdapterMock = M::mock(HttpAdapter::class);
		$requestMethod = 'GET';
		$requestUrl = 'https://example.com/';
		$request = new Request($requestMethod, $requestUrl);

		$httpAdapterMock
			->expects('prepare')
			->withNoArgs()
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('setUrl')
			->with($requestUrl)
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('setMethod')
			->with($requestMethod)
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('setHeaders')
			->with([])
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('setBody')
			->with(null)
			->andReturnSelf()
		;
		$httpAdapterMock
			->expects('send')
			->withNoArgs()
			->andReturn(false)
		;
		$httpAdapterMock
			->expects('getLastError')
			->withNoArgs()
			->andReturn("test")
		;
		$httpAdapterMock
			->expects('cleanup')
			->once()
			->withNoArgs()
		;

		$client = new Client($httpAdapterMock);

		$this->expectException(ClientException::class);

		$client->execute($request);
	}
}
