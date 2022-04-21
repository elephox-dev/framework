<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Stream\StringStream;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\Response
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Http\ResponseCode
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\AbstractMessage
 * @covers \Elephox\Http\AbstractMessageBuilder
 * @covers \Elephox\Http\ResponseBuilder
 *
 * @internal
 */
class ResponseTest extends TestCase
{
	public function testWith(): void
	{
		$response = new Response('2.0', new HeaderMap(), new StringStream('body'), ResponseCode::OK, null, null);
		$builder = $response->with();

		static::assertInstanceOf(ResponseBuilder::class, $builder);

		$builder->responseCode(ResponseCode::OK);
		$builder->addHeader('X-Foo', 'bar');

		$newResponse = $builder->get();

		static::assertEquals(ResponseCode::OK, $newResponse->getResponseCode());
		static::assertEquals(['bar'], $newResponse->getHeaderMap()->get('X-Foo'));

		$newNewResponse = $newResponse->with()->exception(new Exception('test'))->get();

		static::assertEquals(ResponseCode::InternalServerError, $newNewResponse->getResponseCode());
		static::assertEquals(['bar'], $newNewResponse->getHeaderMap()->get('X-Foo'));
		static::assertEquals('test', $newNewResponse->getException()?->getMessage());
	}
}
