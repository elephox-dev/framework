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
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Http\AbstractMessage
 * @covers \Elephox\Http\AbstractMessageBuilder
 * @covers \Elephox\Http\RequestBuilder
 * @covers \Elephox\Http\RequestMethod
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Http\UrlBuilder
 * @covers \Elephox\OOR\Casing
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Http\CustomRequestMethod
 * @covers \Elephox\Http\UrlScheme
 * @covers \Elephox\Http\HeaderName
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
class RequestTest extends RequestIntegrationTest
{
	protected $skippedTests = [
		'testRequestTarget' => 'RequestTarget is not supported',
		'testGetRequestTargetInOriginFormNormalizesUriWithMultipleLeadingSlashesInPath' => 'RequestTarget is not supported',
	];

	public function createSubject(): RequestInterface|ElephoxRequest
	{
		return Request::build()
			->requestUrl(Url::fromString('/'))
			->get()
		;
	}
}
