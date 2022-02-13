<?php
declare(strict_types=1);

namespace Elephox\Stream;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Stream\ResourceStream
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

		self::assertTrue($stream->isReadable());
		self::assertTrue($stream->isSeekable());
		self::assertFalse($stream->isWriteable());
		self::assertEquals(0, $stream->getSize());
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

		self::assertEquals(0, $stream->getSize());

		$stream->read(1);

		self::assertEquals(0, $stream->getSize());

		$stream->write('a');

		self::assertEquals(1, $stream->getSize());
	}

	public function testGetInvalidSizeFromFstat(): void
	{
		$fh = fopen('php://output', 'rb');
		$stream = new ResourceStream($fh);

		self::assertNull($stream->getSize());
	}

	public function testReadFromInvalidStream(): void
	{
		$fh = fopen('php://output', 'rb');
		$stream = new ResourceStream($fh);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage("Unable to read from stream");

		$stream->read(1);
	}

	public function testStreamGetsDetachedOnceClosed(): void
	{
		$fh = tmpfile();
		$stream = new ResourceStream($fh);

		self::assertIsResource($stream->getResource());

		$stream->close();

		self::assertIsNotResource($stream->getResource());
		self::assertNull($stream->detach());

		$stream->close();
		self::assertNull($stream->detach());
	}
}
