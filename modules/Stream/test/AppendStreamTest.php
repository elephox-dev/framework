<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;
use Mockery as M;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Elephox\Stream\AppendStream
 *
 * @internal
 */
class AppendStreamTest extends MockeryTestCase
{
	public function testGetContents(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getContents')->andReturns('foo');
		$appendedStreamMock->expects('getContents')->andReturns('bar');

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertEquals('foobar', $appendStream->getContents());
	}

	public function testToString(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('__toString')->andReturns('foo');
		$appendedStreamMock->expects('__toString')->andReturns('bar');

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertEquals('foobar', (string) $appendStream);
	}

	public function testDetach(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('detach')->andReturns(1);
		$appendedStreamMock->expects('detach')->andReturns(2);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertEquals(1, $appendStream->detach());
	}

	public function testClose(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('close')->andReturns();
		$appendedStreamMock->expects('close')->andReturns();

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		$appendStream->close();
	}

	public function testGetSize(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getSize')->andReturns(1);
		$appendedStreamMock->expects('getSize')->andReturns(2);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertEquals(3, $appendStream->getSize());
	}

	public function testGetSizeNull(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getSize')->andReturns(null);
		$appendedStreamMock->expects('getSize')->andReturns(2);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertNull($appendStream->getSize());
	}

	public function testReadTell(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getSize')->andReturns(3);
		$appendedStreamMock->expects('getSize')->andReturns(3);

		$streamMock->expects('eof')->andReturns(false);
		$streamMock->expects('tell')->andReturns(0);

		$streamMock->expects('read')->with(3)->andReturns('abc');
		$appendedStreamMock->expects('read')->with(2)->andReturns('de');

		$streamMock->expects('eof')->andReturns(true);
		$streamMock->expects('getSize')->andReturns(3);
		$appendedStreamMock->expects('tell')->andReturns(2);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertEquals('abcde', $appendStream->read(5));
		static::assertEquals(5, $appendStream->tell());
	}

	public function testEof(): void
	{
		$streamMock = M::mock(Stream::class);

		$streamMock->expects('eof')->andReturns(false);

		$appendStream = new AppendStream($streamMock, M::mock(Stream::class));
		static::assertFalse($appendStream->eof());
	}

	public function testEofAppended(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('eof')->andReturns(true);
		$appendedStreamMock->expects('eof')->andReturns(false);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertFalse($appendStream->eof());
	}

	public function testIsSeekableWriteableReadable(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getSize')->andReturns(1);
		$streamMock->expects('isSeekable')->andReturns(true);
		$streamMock->expects('isWriteable')->andReturns(true);
		$streamMock->expects('isReadable')->andReturns(true);

		$appendedStreamMock->expects('getSize')->andReturns(1);
		$appendedStreamMock->expects('isSeekable')->andReturns(true);
		$appendedStreamMock->expects('isWriteable')->andReturns(true);
		$appendedStreamMock->expects('isReadable')->andReturns(true);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertTrue($appendStream->isSeekable());
		static::assertTrue($appendStream->isWriteable());
		static::assertTrue($appendStream->isReadable());
	}

	public function testSeek(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getSize')->andReturns(1);
		$appendedStreamMock->expects('getSize')->andReturns(1);

		$streamMock->expects('eof')->andReturns(false);
		$streamMock->expects('tell')->andReturns(0);

		$streamMock->expects('seek')->with(1, SEEK_SET)->andReturns();

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		$appendStream->seek(1);
	}

	public function testRewind(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('rewind')->andReturns();
		$appendedStreamMock->expects('rewind')->andReturns();

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		$appendStream->rewind();
	}

	public function testWrite(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('write')->with('ab')->andReturns(1);
		$appendedStreamMock->expects('write')->with('b')->andReturns(1);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertEquals(2, $appendStream->write('ab'));
	}

	public function testGetMetadata(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getMetadata')->andReturns(['foo' => 'bar']);
		$appendedStreamMock->expects('getMetadata')->andReturns(['foo' => 'bar']);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertEquals([['foo' => 'bar'], ['foo' => 'bar']], $appendStream->getMetadata());
	}
}
