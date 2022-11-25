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
 *
 * @internal
 */
class ResponseTest extends ResponseIntegrationTest
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
