<?php
declare(strict_types=1);

namespace Elephox\Stream;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Stream\StringStream
 */
class StringStreamTest extends TestCase
{
	public function testConstructor() {
		$stream = new StringStream('foo');

		self::assertEquals('foo', $stream->getContents());
		self::assertTrue($stream->isReadable());
		self::assertTrue($stream->isSeekable());
		self::assertFalse($stream->isWriteable());
	}

	public function testToString(): void
	{
		$stream = new StringStream('foo');

		self::assertEquals('foo', (string) $stream);
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

		self::assertEquals(3, $stream->getSize());
	}

	public function testTell(): void
	{
		$stream = new StringStream('foo');

		self::assertEquals(0, $stream->tell());

		$stream->read(1);

		self::assertEquals(1, $stream->tell());
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

		self::assertEquals(1, $stream->tell());

		$stream->seek(0);

		self::assertEquals(0, $stream->tell());
	}

	public function testSeekCurOffset(): void
	{
		$stream = new StringStream('foo');

		$stream->seek(1);

		self::assertEquals(1, $stream->tell());

		$stream->seek(1, SEEK_CUR);

		self::assertEquals(2, $stream->tell());
	}

	public function testSeekEndOffset(): void
	{
		$stream = new StringStream('foo');

		$stream->seek(1, SEEK_END);

		self::assertEquals(4, $stream->tell());
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

		self::assertEquals(0, $stream->tell());
	}

	public function testWrite(): void
	{
		$stream = new StringStream('foo', writeable: true);

		$stream->write('bar');

		self::assertEquals('foobar', $stream->getContents());
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

		self::assertEquals('foo', $stream->read(3));
		self::assertEquals(3, $stream->tell());
		self::assertEquals('', $stream->read(3));
		self::assertEquals(3, $stream->tell());
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

		self::assertEquals('foo', $stream->getContents());
	}

	public function testGetMetadata(): void
	{
		$stream = new StringStream('foo');

		self::assertEquals([], $stream->getMetadata());
	}
}
