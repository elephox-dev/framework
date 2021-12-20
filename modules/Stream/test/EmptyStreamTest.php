<?php
declare(strict_types=1);

namespace Elephox\Stream;

use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Stream\EmptyStream
 */
class EmptyStreamTest extends TestCase
{
	public function testToString(): void
	{
		$stream = new EmptyStream();

		self::assertSame('', (string) $stream);
	}

	public function testDetach(): void
	{
		$stream = new EmptyStream();

		self::assertNull($stream->detach());
	}

	public function testClose(): void
	{
		$stream = new EmptyStream();

		$stream->close();

		self::assertTrue(true);
	}

	public function testGetSize(): void
	{
		$stream = new EmptyStream();

		self::assertSame(0, $stream->getSize());
	}

	public function testTell(): void
	{
		$stream = new EmptyStream();

		self::assertSame(0, $stream->tell());
	}

	public function testEof(): void
	{
		$stream = new EmptyStream();

		self::assertTrue($stream->eof());
	}

	public function testIsSeekable(): void
	{
		$stream = new EmptyStream();

		self::assertFalse($stream->isSeekable());
	}

	public function testSeek(): void
	{
		$this->expectException(RuntimeException::class);

		$stream = new EmptyStream();
		$stream->seek(0);
	}

	public function testRewind(): void
	{
		$this->expectException(RuntimeException::class);

		$stream = new EmptyStream();
		$stream->rewind();
	}

	public function testIsWritable(): void
	{
		$stream = new EmptyStream();

		self::assertFalse($stream->isWritable());
	}

	public function testWrite(): void
	{
		$this->expectException(RuntimeException::class);

		$stream = new EmptyStream();
		$stream->write('foo');
	}

	public function testIsReadable(): void
	{
		$stream = new EmptyStream();

		self::assertFalse($stream->isReadable());
	}

	public function testRead(): void
	{
		$this->expectException(RuntimeException::class);

		$stream = new EmptyStream();
		$stream->read(1);
	}

	public function testGetContents(): void
	{
		$stream = new EmptyStream();

		self::assertSame('', $stream->getContents());
	}

	public function testGetMetadata(): void
	{
		$stream = new EmptyStream();

		self::assertNull($stream->getMetadata());
	}
}
