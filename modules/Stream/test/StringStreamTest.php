<?php
declare(strict_types=1);

namespace Elephox\Stream;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Stream\AbstractStream
 *
 * @internal
 */
class StringStreamTest extends TestCase
{
	public function testFrom(): void
	{
		$stream = StringStream::from('foo');

		static::assertSame('foo', $stream->getContents());
		static::assertTrue($stream->isReadable());
		static::assertFalse($stream->isWriteable());
		static::assertTrue($stream->isSeekable());
	}

	public function testConstructor(): void
	{
		$stream = new StringStream('foo');

		static::assertSame('foo', $stream->getContents());
		static::assertTrue($stream->isReadable());
		static::assertTrue($stream->isSeekable());
		static::assertFalse($stream->isWriteable());
	}

	public function testToString(): void
	{
		$stream = new StringStream('foo');

		static::assertSame('foo', (string) $stream);
	}

	public function testDetach(): void
	{
		$stream = new StringStream('foo');

		$stream->detach();

		$this->expectException(RuntimeException::class);

		$stream->close();
	}

	public function testClose(): void
	{
		$stream = new StringStream('foo');

		$stream->close();

		$this->expectException(RuntimeException::class);

		$stream->close();
	}

	public function testGetSize(): void
	{
		$stream = new StringStream('foo');

		static::assertSame(3, $stream->getSize());
	}

	public function testTell(): void
	{
		$stream = new StringStream('foo');

		static::assertSame(0, $stream->tell());

		$stream->read(1);

		static::assertSame(1, $stream->tell());
	}

	public function testEof(): void
	{
		$stream = new StringStream('foo');

		static::assertFalse($stream->eof());

		$stream->read(3);

		static::assertTrue($stream->eof());
	}

	public function testSeek(): void
	{
		$stream = new StringStream('foo');

		$stream->seek(1);

		static::assertSame(1, $stream->tell());

		$stream->seek(0);

		static::assertSame(0, $stream->tell());
	}

	public function testSeekCurOffset(): void
	{
		$stream = new StringStream('foo');

		$stream->seek(1);

		static::assertSame(1, $stream->tell());

		$stream->seek(1, SEEK_CUR);

		static::assertSame(2, $stream->tell());
	}

	public function testSeekEndOffset(): void
	{
		$stream = new StringStream('foo');

		$stream->seek(1, SEEK_END);

		static::assertSame(4, $stream->tell());
	}

	public function testSeekNotSeekable(): void
	{
		$stream = new StringStream('foo', seekable: false);

		$this->expectException(RuntimeException::class);

		$stream->seek(1);
	}

	public function testSeekInvalidWhence(): void
	{
		$stream = new StringStream('foo');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid whence: 3');

		$stream->seek(1, 3);
	}

	public function testRewind(): void
	{
		$stream = new StringStream('foo');

		$stream->seek(1);

		$stream->rewind();

		static::assertSame(0, $stream->tell());
	}

	public function testWrite(): void
	{
		$stream = new StringStream('foo', writeable: true);

		$stream->write('bar');

		static::assertSame('foobar', $stream->getContents());
	}

	public function testWriteNotWritable(): void
	{
		$stream = new StringStream('foo');

		$this->expectException(RuntimeException::class);

		$stream->write('bar');
	}

	public function testRead(): void
	{
		$stream = new StringStream('foo');

		static::assertSame('foo', $stream->read(3));
		static::assertSame(3, $stream->tell());
		static::assertSame('', $stream->read(3));
		static::assertSame(3, $stream->tell());
	}

	public function testReadNotReadable(): void
	{
		$stream = new StringStream('foo', readable: false);

		$this->expectException(RuntimeException::class);

		$stream->read(3);
	}

	public function testGetContents(): void
	{
		$stream = new StringStream('foo');

		static::assertSame('foo', $stream->getContents());
	}

	public function testGetMetadata(): void
	{
		$stream = new StringStream('foo');

		static::assertSame([], $stream->getMetadata());
	}

	public function testReadLine(): void
	{
		$stream = new StringStream("foo\r\nbar\r\nbaz");

		static::assertSame('foo', $stream->readLine());
		static::assertSame('bar', $stream->readLine());
		static::assertSame('baz', $stream->readLine());
	}

	public function testReadAllLines(): void
	{
		$stream = new StringStream("foo\r\nbar\r\nbaz");

		static::assertSame(['foo', 'bar', 'baz'], iterator_to_array($stream->readAllLines()));
	}

	public function testReadByte(): void
	{
		$stream = new StringStream('foo');

		static::assertSame(102, $stream->readByte());
		static::assertSame(1, $stream->tell());
	}
}
