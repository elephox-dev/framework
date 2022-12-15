<?php
declare(strict_types=1);

namespace Elephox\Http\PSR7;

use Elephox\Http\Contract\ServerRequest as ElephoxServerRequest;
use Elephox\Http\ServerRequest;
use Elephox\Http\Url;
use Http\Psr7Test\ServerRequestIntegrationTest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @covers \Elephox\Http\ServerRequest
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\IteratorProvider
 * @covers \Elephox\Collection\Iterator\SelectIterator
 * @covers \Elephox\Collection\ObjectMap
 * @covers \Elephox\Http\AbstractMessage
 * @covers \Elephox\Http\ParameterMap
 * @covers \Elephox\Http\Request
 * @covers \Elephox\Http\RequestBuilder
 * @covers \Elephox\Http\ServerRequestBuilder
 * @covers \Elephox\Http\AbstractMessageBuilder
 * @covers \Elephox\Http\Url
 * @covers \Elephox\Http\UrlBuilder
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Http\AbstractMessageBuilder
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Http\Cookie
 * @covers \Elephox\Http\HeaderName
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
class ServerRequestTest extends ServerRequestIntegrationTest
{
	protected $skippedTests = [
		'testGetServerParams' => "Elephox doesn't load the environment server params",
	];

	public function createSubject(): ElephoxServerRequest|ServerRequestInterface
	{
		return ServerRequest::build()
			->requestUrl(Url::fromString('/'))
			->get()
		;
	}

	public function validParsedBodyParams(): array
	{
		$bodies = parent::validParsedBodyParams();

		// Elephox bodies get serialized and deserialized so its impossible to re-create stdClass
		unset($bodies[1]);

		return $bodies;
	}
}
