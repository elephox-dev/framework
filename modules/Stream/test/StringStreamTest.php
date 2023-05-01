<?php
declare(strict_types=1);

namespace Elephox\Stream;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Stream\StreamReader
 *
 * @internal
 */
final class StringStreamTest extends TestCase
{
	public function testFrom(): void
	{
		$stream = StringStream::from('foo');

		self::assertSame('foo', $stream->getContents());
		self::assertTrue($stream->isReadable());
		self::assertFalse($stream->isWritable());
		self::assertTrue($stream->isSeekable());
	}

	public function testConstructor(): void
	{
		$stream = new StringStream('foo');

		self::assertSame('foo', $stream->getContents());
		self::assertTrue($stream->isReadable());
		self::assertTrue($stream->isSeekable());
		self::assertFalse($stream->isWritable());
	}

	public function testToString(): void
	{
		$stream = new StringStream('foo');

		self::assertSame('foo', (string) $stream);
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

		self::assertSame(3, $stream->getSize());
	}

	public function testTell(): void
	{
		$stream = new StringStream('foo');

		self::assertSame(0, $stream->tell());

		$stream->read(1);

		self::assertSame(1, $stream->tell());
	}

	public function testEof(): void
	{
		$stream = new StringStream('foo');

		self::assertFalse($stream->eof());

		$stream->read(3);

		self::assertTrue($stream->eof());
	}

	public function testSeek(): void
	{
		$stream = new StringStream('foo');

		$stream->seek(1);

		self::assertSame(1, $stream->tell());

		$stream->seek(0);

		self::assertSame(0, $stream->tell());
	}

	public function testSeekCurOffset(): void
	{
		$stream = new StringStream('foo');

		$stream->seek(1);

		self::assertSame(1, $stream->tell());

		$stream->seek(1, SEEK_CUR);

		self::assertSame(2, $stream->tell());
	}

	public function testSeekEndOffset(): void
	{
		$stream = new StringStream('foo');

		$stream->seek(1, SEEK_END);

		self::assertSame(4, $stream->tell());
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

		self::assertSame(0, $stream->tell());
	}

	public function testWrite(): void
	{
		$stream = new StringStream('foo', writable: true);

		$stream->write('bar');

		self::assertSame('foobar', $stream->getContents());
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

		self::assertSame('foo', $stream->read(3));
		self::assertSame(3, $stream->tell());
		self::assertSame('', $stream->read(3));
		self::assertSame(3, $stream->tell());
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

		self::assertSame('foo', $stream->getContents());
	}

	public function testGetMetadata(): void
	{
		$stream = new StringStream('foo');

		self::assertSame([], $stream->getMetadata());
	}

	public function testReadLine(): void
	{
		$stream = new StringStream("foo\r\nbar\r\nbaz");

		self::assertSame('foo', $stream->readLine());
		self::assertSame('bar', $stream->readLine());
		self::assertSame('baz', $stream->readLine());
		$stream->rewind();
		self::assertSame('foo', $stream->readLine());
	}

	public function testReadAllLines(): void
	{
		$stream = new StringStream("foo\r\nbar\r\nbaz");

		self::assertSame(['foo', 'bar', 'baz'], [...$stream->readAllLines()]);
	}

	public function testReadBytes(): void
	{
		$stream = new StringStream('foo');

		self::assertSame(102, $stream->readByte());
		self::assertSame(1, $stream->tell());

		self::assertSame([111, 111], [...$stream->readBytes(2)]);
		self::assertTrue($stream->eof());
	}

	public function testReadChar(): void
	{
		$simpleStream = new StringStream('hello');

		self::assertSame('h', $simpleStream->readChar());
		self::assertSame('e', $simpleStream->readChar());
		self::assertSame('l', $simpleStream->readChar());
		self::assertSame('l', $simpleStream->readChar());
		self::assertSame('o', $simpleStream->readChar());
		self::assertTrue($simpleStream->eof());

		$multiByteStream = new StringStream('🧔+👩🏿=❤');
		self::assertSame('🧔', $multiByteStream->readChar());
		self::assertSame('+', $multiByteStream->readChar());
		self::assertSame('👩', $multiByteStream->readChar());
		self::assertSame('🏿', $multiByteStream->readChar());
		self::assertSame('=', $multiByteStream->readChar());
		self::assertSame('❤', $multiByteStream->readChar());
		self::assertTrue($multiByteStream->eof());
	}
}
