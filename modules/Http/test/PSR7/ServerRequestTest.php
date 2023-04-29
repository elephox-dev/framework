<?php
declare(strict_types=1);

namespace Elephox\Http\PSR7;

use Elephox\Http\Contract\ServerRequest as ElephoxServerRequest;
use Elephox\Http\ServerRequestBuilder;
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
 * @covers \Elephox\Http\SessionMap
 * @covers \Elephox\Http\CookieMap
 * @covers \Elephox\Http\UploadedFileMap
 * @covers \Elephox\OOR\Str
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Http\CustomRequestMethod
 * @covers \Elephox\OOR\Casing
 * @covers \Elephox\Http\UrlScheme
 * @covers \Elephox\Http\RequestMethod
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
class ServerRequestTest extends ServerRequestIntegrationTest
{
	protected $skippedTests = [
		'testRequestTarget' => 'withRequestTarget is not supported',
	];

	public function createSubject(): ElephoxServerRequest|ServerRequestInterface
	{
		return ServerRequestBuilder::fromGlobals(requestUrl: Url::fromString('/'));
	}

	public function validParsedBodyParams(): array
	{
		$bodies = parent::validParsedBodyParams();

		// Elephox bodies get serialized and deserialized so its impossible to re-create stdClass
		unset($bodies[1]);

		return $bodies;
	}
}
