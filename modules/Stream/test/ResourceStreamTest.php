<?php
declare(strict_types=1);

namespace Elephox\Stream;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Stream\ResourceStream
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
		$stream = new ResourceStream($fh);

		static::assertTrue($stream->isReadable());
		static::assertTrue($stream->isSeekable());
		static::assertFalse($stream->isWriteable());
		static::assertEquals(0, $stream->getSize());
	}

	public function testConstructorNoResource(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new ResourceStream(null);
	}

	public function testGetSizeFromFstat(): void
	{
		$fh = tmpfile();
		$stream = new ResourceStream($fh, writeable: true);

		static::assertEquals(0, $stream->getSize());

		$stream->read(1);

		static::assertEquals(0, $stream->getSize());

		$stream->write('a');

		static::assertEquals(1, $stream->getSize());
	}

	public function testGetInvalidSizeFromFstat(): void
	{
		$fh = fopen('php://output', 'rb');
		$stream = new ResourceStream($fh);

		static::assertNull($stream->getSize());
	}

	public function testReadFromInvalidStream(): void
	{
		$fh = fopen('php://output', 'rb');
		$stream = new ResourceStream($fh);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Unable to read from stream');

		$stream->read(1);
	}

	public function testStreamGetsDetachedOnceClosed(): void
	{
		$fh = tmpfile();
		$stream = new ResourceStream($fh);

		static::assertIsResource($stream->getResource());

		$stream->close();

		static::assertIsNotResource($stream->getResource());
		static::assertNull($stream->detach());

		$stream->close();
		static::assertNull($stream->detach());
	}
}
