<?php
declare(strict_types=1);

namespace Elephox\Http\PSR7;

use Elephox\Http\Contract\Response as ElephoxResponse;
use Elephox\Http\Response;
use Elephox\Http\ResponseCode;
use Http\Psr7Test\ResponseIntegrationTest;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers \Elephox\Http\Response
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Http\AbstractMessage
 * @covers \Elephox\Http\AbstractMessageBuilder
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Http\ResponseBuilder
 * @covers \Elephox\OOR\Casing
 * @covers \Elephox\Http\ResponseCode
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
final class ResponseTest extends ResponseIntegrationTest
{
	protected $skippedTests = [
		'testReasonPhrase' => 'non-standard reason phrases are not supported',
	];

	public function createSubject(): ElephoxResponse|ResponseInterface
	{
		return Response::build()
			->responseCode(ResponseCode::OK)
			->get()
		;
	}
}
