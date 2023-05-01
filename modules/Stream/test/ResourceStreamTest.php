<?php
declare(strict_types=1);

namespace Elephox\Stream;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\OOR\Str
 *
 * @internal
 */
final class ResourceStreamTest extends TestCase
{
	private string $tmpName;

	public function setUp(): void
	{
		$this->tmpName = tempnam(sys_get_temp_dir(), 'elephox-text-');
	}

	public function testConstructor(): void
	{
		$fh = fopen($this->tmpName, 'rb');
		$stream = ResourceStream::wrap($fh);

		self::assertTrue($stream->isReadable());
		self::assertTrue($stream->isSeekable());
		self::assertFalse($stream->isWritable());
		self::assertSame(0, $stream->getSize());
	}

	public function testConstructorNoResource(): void
	{
		$this->expectException(InvalidArgumentException::class);

		ResourceStream::wrap(null);
	}

	public function testGetSizeFromFstat(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, writable: true);

		self::assertSame(0, $stream->getSize());

		$stream->read(1);

		self::assertSame(0, $stream->getSize());

		$stream->write('a');

		self::assertSame(1, $stream->getSize());
	}

	public function testGetInvalidSizeFromFstat(): void
	{
		$fh = fopen('php://output', 'rb');
		$stream = ResourceStream::wrap($fh);

		self::assertNull($stream->getSize());
	}

	public function testReadFromInvalidStream(): void
	{
		$fh = fopen('php://output', 'rb');
		$stream = ResourceStream::wrap($fh);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Cannot read from a non-readable resource');

		$stream->read(1);
	}

	public function testStreamGetsDetachedOnceClosed(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh);

		self::assertIsResource($stream->getResource());

		$stream->close();

		self::assertIsNotResource($stream->getResource());
		self::assertNull($stream->detach());

		$stream->close();
		self::assertNull($stream->detach());
	}

	public function testToString(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, writable: true);

		self::assertSame('', (string) $stream);

		$stream->write('a');

		self::assertSame('a', (string) $stream);

		$stream->close();

		self::assertSame('', (string) $stream);
	}

	public function testClosedGetSizeThrows(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh);

		$stream->close();

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Resource is not available');

		$stream->getSize();
	}

	public function testClosedTellThrows(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh);

		$stream->close();

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Resource is not available');

		$stream->tell();
	}

	public function testClosedEofThrows(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh);

		$stream->close();

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Resource is not available');

		$stream->eof();
	}

	public function testClosedSeekThrows(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh);

		$stream->close();

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Resource is not available');

		$stream->seek(1);
	}

	public function testClosedRewindThrows(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh);

		$stream->close();

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Resource is not available');

		$stream->rewind();
	}

	public function testClosedWriteThrows(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh);

		$stream->close();

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Resource is not available');

		$stream->write('test');
	}

	public function testClosedReadThrows(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh);

		$stream->close();

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Resource is not available');

		$stream->read(1);
	}

	public function testClosedGetContentsThrows(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh);

		$stream->close();

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Resource is not available');

		$stream->getContents();
	}

	public function testClosedGetMetadataIsEmpty(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh);

		$stream->close();

		self::assertEmpty($stream->getMetadata());
		self::assertNull($stream->getMetadata('size'));
	}

	public function testInvalidLengthReadThrows(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Length parameter cannot be negative');

		$stream->read(-1);
	}

	public function testTell(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, writable: true);

		self::assertSame(0, $stream->tell());

		$stream->write('a');

		self::assertSame(1, $stream->tell());
	}

	public function testEof(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, writable: true);

		self::assertEmpty($stream->getContents());
		self::assertTrue($stream->eof());

		$stream->write('a');

		self::assertTrue($stream->eof());
		$stream->rewind();
		self::assertFalse($stream->eof());
	}

	public function testSeekThrowsIfNotSeekable(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, seekable: false);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Resource is not seekable');

		$stream->seek(1);
	}

	public function testSeekThrowsForInvalidOffset(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, seekable: true);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Unable to seek to resource position -1 with whence 0');

		$stream->seek(-1);
	}

	public function testWriteThrowsIfNotWriteable(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, writable: false);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Cannot write to a non-writable resource');

		$stream->write('test');
	}

	public function testReadThrowsIfNotReadable(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, readable: false);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Cannot read from a non-readable resource');

		$stream->read(1);
	}

	public function testReadReturnsEmptyStringForZeroLength(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, readable: true);

		self::assertSame('', $stream->read(0));
	}

	public function testGetMetadata(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, readable: true);

		$data = $stream->getMetadata();
		self::assertIsArray($data);
		self::assertArrayHasKey('eof', $data);
		self::assertArrayHasKey('seekable', $data);
		self::assertArrayHasKey('mode', $data);
		self::assertArrayHasKey('uri', $data);
		self::assertArrayHasKey('timed_out', $data);
		self::assertArrayHasKey('blocked', $data);
		self::assertArrayHasKey('wrapper_type', $data);
		self::assertArrayHasKey('stream_type', $data);

		self::assertTrue($stream->getMetadata('seekable'));
		self::assertFalse($stream->getMetadata('eof'));
		self::assertSame('plainfile', $stream->getMetadata('wrapper_type'));
		self::assertNull($stream->getMetadata('non-existent'));
	}
}
