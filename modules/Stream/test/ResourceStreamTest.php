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
class ResourceStreamTest extends TestCase
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

		static::assertTrue($stream->isReadable());
		static::assertTrue($stream->isSeekable());
		static::assertFalse($stream->isWritable());
		static::assertSame(0, $stream->getSize());
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

		static::assertSame(0, $stream->getSize());

		$stream->read(1);

		static::assertSame(0, $stream->getSize());

		$stream->write('a');

		static::assertSame(1, $stream->getSize());
	}

	public function testGetInvalidSizeFromFstat(): void
	{
		$fh = fopen('php://output', 'rb');
		$stream = ResourceStream::wrap($fh);

		static::assertNull($stream->getSize());
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

		static::assertIsResource($stream->getResource());

		$stream->close();

		static::assertIsNotResource($stream->getResource());
		static::assertNull($stream->detach());

		$stream->close();
		static::assertNull($stream->detach());
	}

	public function testToString(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, writable: true);

		static::assertSame('', (string) $stream);

		$stream->write('a');

		static::assertSame('a', (string) $stream);

		$stream->close();

		static::assertSame('', (string) $stream);
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

		static::assertEmpty($stream->getMetadata());
		static::assertNull($stream->getMetadata('size'));
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

		static::assertSame(0, $stream->tell());

		$stream->write('a');

		static::assertSame(1, $stream->tell());
	}

	public function testEof(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, writable: true);

		static::assertEmpty($stream->getContents());
		static::assertTrue($stream->eof());

		$stream->write('a');

		static::assertTrue($stream->eof());
		$stream->rewind();
		static::assertFalse($stream->eof());
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

		static::assertSame('', $stream->read(0));
	}

	public function testGetMetadata(): void
	{
		$fh = tmpfile();
		$stream = ResourceStream::wrap($fh, readable: true);

		$data = $stream->getMetadata();
		static::assertIsArray($data);
		static::assertArrayHasKey('eof', $data);
		static::assertArrayHasKey('seekable', $data);
		static::assertArrayHasKey('mode', $data);
		static::assertArrayHasKey('uri', $data);
		static::assertArrayHasKey('timed_out', $data);
		static::assertArrayHasKey('blocked', $data);
		static::assertArrayHasKey('wrapper_type', $data);
		static::assertArrayHasKey('stream_type', $data);

		static::assertTrue($stream->getMetadata('seekable'));
		static::assertFalse($stream->getMetadata('eof'));
		static::assertSame('plainfile', $stream->getMetadata('wrapper_type'));
		static::assertNull($stream->getMetadata('non-existent'));
	}
}
