<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as M;

/**
 * @covers \Elephox\Stream\LazyStream
 * @covers \Elephox\Stream\StringStream
 */
class LazyStreamTest extends MockeryTestCase
{

	public function testConstructor(): void
	{
		$streamMock = M::mock(Stream::class);

		$stream = new LazyStream(fn () => $streamMock);

		self::assertTrue($stream->isReadable());
		self::assertTrue($stream->isSeekable());
		self::assertFalse($stream->isWriteable());
	}

	public function testGetStreamOnlyExecutesOnce()
	{
		$streamMock = M::mock(Stream::class);

		$streamMock
			->expects('isReadable')
			->withNoArgs()
			->andReturn(true)
		;

		$stream = new LazyStream(fn() => $streamMock);

		self::assertTrue($stream->isReadable());
		self::assertTrue($stream->isReadable());
		self::assertSame($streamMock, $stream->getStream());
		self::assertTrue($stream->isReadable());
	}
}
