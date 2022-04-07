<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Mimey\MimeType;
use Elephox\Stream\StringStream;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\ResponseBuilder
 * @covers \Elephox\Http\Response
 * @covers \Elephox\Http\ResponseCode
 * @covers \Elephox\Mimey\MimeType
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\AbstractMessageBuilder
 * @covers \Elephox\Http\AbstractMessage
 *
 * @internal
 */
class ResponseBuilderTest extends TestCase
{
	public function testBuild(): void
	{
		$builder = Response::build();
		$builder->responseCode(ResponseCode::OK);
		$builder->contentType(MimeType::TextPlain);
		$builder->header('X-Foo', ['bar']);
		$builder->body(new StringStream('Hello World'));

		$response = $builder->get();

		static::assertEquals(ResponseCode::OK, $response->getResponseCode());
	}
}
