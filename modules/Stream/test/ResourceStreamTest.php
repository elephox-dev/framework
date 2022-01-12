<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Files\Contract\Directory;
use Elephox\Files\Contract\File;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as M;
use RuntimeException;

/**
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Files\File
 * @covers \Elephox\Files\Directory
 * @covers \Elephox\Stream\UnreadableFileException
 * @covers \Elephox\Stream\ReadOnlyFileException
 * @covers \Elephox\Stream\ReadonlyParentException
 * @covers \Elephox\Files\FileException
 */
class ResourceStreamTest extends MockeryTestCase
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

	public function testFromFile(): void
	{
		$stream = ResourceStream::fromFile($this->tmpName);

		self::assertInstanceOf(ResourceStream::class, $stream);
		self::assertTrue($stream->isReadable());
		self::assertTrue($stream->isSeekable());
		self::assertFalse($stream->isWriteable());

		$stream->close();
	}

	/** @noinspection PhpConditionAlreadyCheckedInspection */
	public function invalidFopenFlagsProvider(): iterable
	{
		$readable = true;
		$writeable = true;
		$create = true;
		$append = true;
		$truncate = true;

		// append & truncate cannot both be true
		yield [ $readable,  $writeable,  $create,  $append,  $truncate];
		yield [!$readable,  $writeable,  $create,  $append,  $truncate];
		yield [ $readable, !$writeable,  $create,  $append,  $truncate];
		yield [!$readable, !$writeable,  $create,  $append,  $truncate];
		yield [ $readable,  $writeable, !$create,  $append,  $truncate];
		yield [!$readable,  $writeable, !$create,  $append,  $truncate];
		yield [ $readable, !$writeable, !$create,  $append,  $truncate];
		yield [!$readable, !$writeable, !$create,  $append,  $truncate];

		// if truncate is true, writeable and create must be true
		yield [ $readable,  $writeable,  !$create, !$append, $truncate];
		yield [ $readable, !$writeable,   $create, !$append, $truncate];
		yield [ $readable, !$writeable,  !$create, !$append, $truncate];
		yield [!$readable,  $writeable,  !$create, !$append, $truncate];
		yield [!$readable, !$writeable,   $create, !$append, $truncate];
		yield [!$readable, !$writeable,  !$create, !$append, $truncate];

		// if append is true, writeable and create must be true
		yield [ $readable,  $writeable,  !$create,  $append, !$truncate];
		yield [ $readable, !$writeable,   $create,  $append, !$truncate];
		yield [ $readable, !$writeable,  !$create,  $append, !$truncate];
		yield [!$readable,  $writeable,  !$create,  $append, !$truncate];
		yield [!$readable, !$writeable,   $create,  $append, !$truncate];
		yield [!$readable, !$writeable,  !$create,  $append, !$truncate];

		// if writable is false, create, append and truncate must be false
		yield [ $readable, !$writeable,  $create, !$append,  $truncate];
		yield [ $readable, !$writeable, !$create, !$append,  $truncate];
		yield [ $readable, !$writeable, !$create,  $append, !$truncate];
		yield [ $readable, !$writeable,  $create, !$append, !$truncate];
	}

	/**
	 * @dataProvider invalidFopenFlagsProvider
	 */
	public function testInvalidFopenFlags(bool $read, bool $write, bool $create, bool $append, bool $truncate): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid combination of flags: readable=' . ($read ?: '0') . ', writeable=' . ($write ?: '0') . ', create=' . ($create ?: '0') . ', append=' . ($append ?: '0') . ', truncate=' . ($truncate ?: '0'));

		ResourceStream::fromFile($this->tmpName, $read, $write, $create, $append, $truncate);
	}

	public function testNonReadableFile(): void
	{
		$fileMock = M::mock(File::class);

		$fileMock
			->expects('isReadable')
			->withNoArgs()
			->andReturn(false)
		;

		$fileMock
			->expects('getPath')
			->withNoArgs()
			->andReturn('/path/to/file')
		;

		$this->expectException(UnreadableFileException::class);

		ResourceStream::fromFile($fileMock);
	}

	public function testNonWriteableFile(): void
	{
		$fileMock = M::mock(File::class);

		$fileMock
			->expects('isReadable')
			->withNoArgs()
			->andReturn(true)
		;

		$fileMock
			->expects('isWritable')
			->withNoArgs()
			->andReturn(false)
		;

		$fileMock
			->expects('getPath')
			->withNoArgs()
			->andReturn('/path/to/file')
		;

		$this->expectException(ReadOnlyFileException::class);

		ResourceStream::fromFile($fileMock, writeable: true);
	}

	public function testNonWriteableFileAppend(): void
	{
		$fileMock = M::mock(File::class);

		$fileMock
			->expects('isReadable')
			->withNoArgs()
			->andReturn(true)
		;

		$fileMock
			->expects('isWritable')
			->withNoArgs()
			->andReturn(false)
		;

		$fileMock
			->expects('getPath')
			->withNoArgs()
			->andReturn('/path/to/file')
		;

		$this->expectException(ReadOnlyFileException::class);

		ResourceStream::fromFile($fileMock, append: true);
	}

	public function testReadonlyParent(): void
	{
		$fileMock = M::mock(File::class);
		$directoryMock = M::mock(Directory::class);

		$fileMock
			->expects('isReadable')
			->withNoArgs()
			->andReturn(true)
		;

		$fileMock
			->expects('isWritable')
			->withNoArgs()
			->andReturn(true)
		;

		$fileMock
			->expects('getPath')
			->withNoArgs()
			->andReturn('/path/to/file')
		;

		$fileMock
			->expects('getParent')
			->withNoArgs()
			->andReturn($directoryMock)
		;

		$directoryMock
			->expects('isReadonly')
			->withNoArgs()
			->andReturn(true)
		;

		$this->expectException(ReadonlyParentException::class);

		ResourceStream::fromFile($fileMock, writeable: true, create: true);
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
