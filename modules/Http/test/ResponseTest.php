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
 * @covers \Elephox\Collection\Iterator\FlipIterator
 *
 * @internal
 */
final class ResponseTest extends TestCase
{
	public function testWith(): void
	{
		$response = new Response('2.0', new HeaderMap(), new StringStream('body'), ResponseCode::OK, null);
		$builder = $response->with();

		self::assertInstanceOf(ResponseBuilder::class, $builder);

		$builder->responseCode(ResponseCode::OK);
		$builder->addedHeader('X-Foo', 'bar');

		$newResponse = $builder->get();

		self::assertSame(ResponseCode::OK, $newResponse->getResponseCode());
		self::assertSame(['bar'], $newResponse->getHeaderMap()->get('X-Foo'));

		$newNewResponse = $newResponse->with()->exception(new Exception('test'))->get();

		self::assertSame(ResponseCode::InternalServerError, $newNewResponse->getResponseCode());
		self::assertSame(['bar'], $newNewResponse->getHeaderMap()->get('X-Foo'));
		self::assertSame('test', $newNewResponse->getException()?->getMessage());
	}
}
