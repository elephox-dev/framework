<?php
declare(strict_types=1);

namespace Elephox\Stream\PSR7;

use Elephox\Stream\ResourceStream;
use Http\Psr7Test\StreamIntegrationTest;

/**
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\OOR\Str
 *
 * @internal
 */
class StreamTest extends StreamIntegrationTest
{
	public function createStream($data): ResourceStream
	{
		return ResourceStream::wrap($data);
	}
}
