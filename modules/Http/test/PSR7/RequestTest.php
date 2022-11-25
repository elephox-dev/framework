<?php
declare(strict_types=1);

namespace Elephox\Http\PSR7;

use Elephox\Http\Contract\Request as ElephoxRequest;
use Elephox\Http\Request;
use Elephox\Http\Url;
use Http\Psr7Test\RequestIntegrationTest;
use Psr\Http\Message\RequestInterface;

/**
 * @covers \Elephox\Http\Request
 *
 * @internal
 */
class RequestTest extends RequestIntegrationTest
{
	protected $skippedTests = [
		'testRequestTarget' => 'RequestTarget is not supported',
	];

	public function createSubject(): RequestInterface|ElephoxRequest
	{
		return Request::build()
			->requestUrl(Url::fromString('/'))
			->get()
		;
	}
}
