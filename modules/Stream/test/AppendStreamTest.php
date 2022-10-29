<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Stream\Contract\Stream;
use InvalidArgumentException;
use Mockery as M;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

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
		static::assertSame('foobar', $appendStream->getContents());
	}

	public function testToString(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('__toString')->andReturns('foo');
		$appendedStreamMock->expects('__toString')->andReturns('bar');

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertSame('foobar', (string) $appendStream);
	}

	public function testDetach(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('detach')->andReturns(1);
		$appendedStreamMock->expects('detach')->andReturns(2);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertSame(1, $appendStream->detach());
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
		static::assertSame(3, $appendStream->getSize());
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
		static::assertSame('abcde', $appendStream->read(5));
		static::assertSame(5, $appendStream->tell());
	}

	public function testReadNullSize(): void
	{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('AppendStream is only readable if the underlying streams sizes are known');

		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getSize')->andReturns(3);
		$appendedStreamMock->expects('getSize')->andReturns(null);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		$appendStream->read(12);
	}

	public function testReadAboveStream(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->allows('getSize')->twice()->withNoArgs()->andReturns(3);
		$appendedStreamMock->expects('getSize')->withNoArgs()->andReturns(3);

		$streamMock->expects('eof')->andReturns(true);

		$appendedStreamMock->expects('tell')->andReturns(1);
		$appendedStreamMock->expects('read')->with(2)->andReturns('de');

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		$appendStream->read(2);
	}

	public function testReadWithinStream(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getSize')->withNoArgs()->andReturns(3);
		$appendedStreamMock->expects('getSize')->withNoArgs()->andReturns(3);

		$streamMock->expects('eof')->andReturns(false);
		$streamMock->expects('tell')->andReturns(1);
		$streamMock->expects('read')->with(2)->andReturns('de');

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		$appendStream->read(2);
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

		$streamMock->allows('getSize')->times(5)->andReturns(10);
		$appendedStreamMock->allows('getSize')->times(4)->andReturns(10);

		$streamMock->expects('seek')->with(10)->andReturns();
		$appendedStreamMock->expects('seek')->with(2)->andReturns();

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		$appendStream->seek(10);
		$appendStream->seek(12);

		$streamMock->expects('eof')->withNoArgs()->andReturns(true);
		$appendedStreamMock->expects('tell')->withNoArgs()->andReturns(2);
		$appendedStreamMock->expects('seek')->with(5)->andReturns();

		$appendStream->seek(3, SEEK_CUR);

		$streamMock->expects('seek')->with(9)->andReturns();

		$appendStream->seek(11, SEEK_END);
	}

	public function testSeekInvalidSize(): void
	{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('AppendStream is only seekable if the underlying streams sizes are known');

		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getSize')->withNoArgs()->andReturns(10);
		$appendedStreamMock->expects('getSize')->withNoArgs()->andReturns(null);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		$appendStream->seek(10);
	}

	public function testSeekInvalidWhence(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid whence');

		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getSize')->withNoArgs()->andReturns(10);
		$appendedStreamMock->expects('getSize')->withNoArgs()->andReturns(10);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		$appendStream->seek(10, SEEK_SET - 1);
	}

	public function testSeekNegativeOffset(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Cannot seek to negative offset');

		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getSize')->withNoArgs()->andReturns(2);
		$appendedStreamMock->expects('getSize')->withNoArgs()->andReturns(1);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		$appendStream->seek(4, SEEK_END);
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
		static::assertSame(2, $appendStream->write('ab'));
	}

	public function testGetMetadata(): void
	{
		$streamMock = M::mock(Stream::class);
		$appendedStreamMock = M::mock(Stream::class);

		$streamMock->expects('getMetadata')->andReturns(['foo' => 'bar']);
		$appendedStreamMock->expects('getMetadata')->andReturns(['foo' => 'bar']);

		$appendStream = new AppendStream($streamMock, $appendedStreamMock);
		static::assertSame([['foo' => 'bar'], ['foo' => 'bar']], $appendStream->getMetadata());
	}
}
