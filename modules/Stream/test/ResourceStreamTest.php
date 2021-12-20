<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Files\Contract\Directory;
use Elephox\Files\Contract\File;
use InvalidArgumentException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as M;

/**
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Files\File
 * @covers \Elephox\Files\Directory
 * @covers \Elephox\Stream\UnreadableFileException
 * @covers \Elephox\Stream\UnwritableFileException
 * @covers \Elephox\Stream\ReadonlyParentException
 */
class ResourceStreamTest extends MockeryTestCase
{
	public function testFromFile(): void
	{
		$stream = ResourceStream::fromFile(__FILE__);

		self::assertInstanceOf(ResourceStream::class, $stream);
		self::assertTrue($stream->isReadable());
		self::assertTrue($stream->isSeekable());
		self::assertFalse($stream->isWritable());

		$stream->close();
	}

	/** @noinspection PhpConditionAlreadyCheckedInspection */
	public function invalidFopenFlagsProvider(): iterable
	{
		$readable = true;
		$writable = true;
		$create = true;
		$append = true;
		$truncate = true;

		// append & truncate cannot both be true
		yield [ $readable,  $writable,  $create,  $append,  $truncate];
		yield [!$readable,  $writable,  $create,  $append,  $truncate];
		yield [ $readable, !$writable,  $create,  $append,  $truncate];
		yield [!$readable, !$writable,  $create,  $append,  $truncate];
		yield [ $readable,  $writable, !$create,  $append,  $truncate];
		yield [!$readable,  $writable, !$create,  $append,  $truncate];
		yield [ $readable, !$writable, !$create,  $append,  $truncate];
		yield [!$readable, !$writable, !$create,  $append,  $truncate];

		// if truncate is true, writeable and create must be true
		yield [ $readable,  $writable,  !$create, !$append, $truncate];
		yield [ $readable, !$writable,   $create, !$append, $truncate];
		yield [ $readable, !$writable,  !$create, !$append, $truncate];
		yield [!$readable,  $writable,  !$create, !$append, $truncate];
		yield [!$readable, !$writable,   $create, !$append, $truncate];
		yield [!$readable, !$writable,  !$create, !$append, $truncate];

		// if append is true, writeable and create must be true
		yield [ $readable,  $writable,  !$create,  $append, !$truncate];
		yield [ $readable, !$writable,   $create,  $append, !$truncate];
		yield [ $readable, !$writable,  !$create,  $append, !$truncate];
		yield [!$readable,  $writable,  !$create,  $append, !$truncate];
		yield [!$readable, !$writable,   $create,  $append, !$truncate];
		yield [!$readable, !$writable,  !$create,  $append, !$truncate];

		// if writable is false, create, append and truncate must be false
		yield [ $readable, !$writable,  $create, !$append,  $truncate];
		yield [ $readable, !$writable, !$create, !$append,  $truncate];
		yield [ $readable, !$writable, !$create,  $append, !$truncate];
		yield [ $readable, !$writable,  $create, !$append, !$truncate];
	}

	/**
	 * @dataProvider invalidFopenFlagsProvider
	 */
	public function testInvalidFopenFlags(bool $read, bool $write, bool $create, bool $append, bool $truncate): void
	{
		$this->expectException(InvalidArgumentException::class);

		ResourceStream::fromFile(__FILE__, $read, $write, $create, $append, $truncate);
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

		$this->expectException(UnwritableFileException::class);

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

		$this->expectException(UnwritableFileException::class);

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

	// TODO: add tests for reading/writing to temporary files via ResourceStream
}
