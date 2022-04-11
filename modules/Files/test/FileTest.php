<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Mimey\MimeType;
use Elephox\Stream\Contract\Stream;
use Elephox\Stream\ResourceStream;
use InvalidArgumentException;
use Mockery as M;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * @covers \Elephox\Files\File
 * @covers \Elephox\Files\Directory
 * @covers \Elephox\Mimey\MimeType
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Files\InvalidParentLevelException
 * @covers \Elephox\Files\FileException
 * @covers \Elephox\Files\FileNotFoundException
 * @covers \Elephox\Files\ReadOnlyFileException
 * @covers \Elephox\Files\UnreadableFileException
 * @covers \Elephox\Files\ReadonlyParentException
 * @covers \Elephox\Files\FileNotCreatedException
 * @covers \Elephox\Files\FilesystemNodeNotFoundException
 * @covers \Elephox\Files\Path
 * @covers \Elephox\Files\AbstractFilesystemNode
 *
 * @internal
 */
class FileTest extends MockeryTestCase
{
	/**
	 * @var resource $fileHandle
	 */
	private $fileHandle;
	private string $filePath;
	private const FileContents = 'This is a generated test file. You are free to delete it.';

	public function setUp(): void
	{
		parent::setUp();

		$this->fileHandle = tmpfile();
		if ($this->fileHandle === false) {
			throw new RuntimeException('Could not create temporary file.');
		}

		$this->filePath = stream_get_meta_data($this->fileHandle)['uri'];

		fwrite($this->fileHandle, self::FileContents);
	}

	public function tearDown(): void
	{
		parent::tearDown();

		if ($this->fileHandle) {
			fclose($this->fileHandle);
		}
	}

	public function testGetExtension(): void
	{
		$file = new File('/tmp/test.txt');
		static::assertEquals('txt', $file->getExtension());
	}

	public function testGetModifiedTime(): void
	{
		$file = new File($this->filePath);
		static::assertEquals(filemtime($this->filePath), $file->getModifiedTime()->getTimestamp());
	}

	public function testFileNotFoundModifiedTime(): void
	{
		$file = new File('/non-existent-file.txt');

		$this->expectException(FileNotFoundException::class);

		$file->getModifiedTime();
	}

	public function testFileNotFoundSize(): void
	{
		$file = new File('/non-existent-file.txt');

		$this->expectException(FileNotFoundException::class);

		$file->getSize();
	}

	public function testFileNotFoundHash(): void
	{
		$file = new File('/non-existent-file.txt');

		$this->expectException(FileNotFoundException::class);

		$file->getHash();
	}

	public function testGetPath(): void
	{
		$file = new File('/tmp/test.txt');
		static::assertEquals('/tmp/test.txt', $file->getPath());
	}

	public function testGetMimeType(): void
	{
		$file = new File($this->filePath);
		static::assertNull($file->getMimeType());

		$fileWithType = new File($this->filePath, MimeType::TextPlain);
		static::assertEquals(MimeType::TextPlain, $fileWithType->getMimeType());
	}

	public function testGetHash(): void
	{
		$file = new File($this->filePath);
		static::assertEquals(md5(self::FileContents), $file->getHash());
	}

	public function testGetSize(): void
	{
		$file = new File($this->filePath);
		static::assertEquals(strlen(self::FileContents), $file->getSize());
	}

	public function testGetParent(): void
	{
		$file = new File('/tmp/nested/deep/file/test.txt');
		$dir = $file->getParent();
		static::assertInstanceOf(Directory::class, $dir);
		static::assertEquals('/tmp/nested/deep/file', $dir->getPath());

		$upperDir = $file->getParent(2);
		static::assertInstanceOf(Directory::class, $upperDir);
		static::assertEquals('/tmp/nested/deep', $upperDir->getPath());

		$this->expectException(InvalidParentLevelException::class);
		$file->getParent(0);
	}

	public function testGetName(): void
	{
		$file = new File('/tmp/test.txt');
		static::assertEquals('test.txt', $file->getName());
	}

	public function testIsExecutable(): void
	{
		$file = new File($this->filePath);
		static::assertFalse($file->isExecutable());
	}

	public function testOpenStream(): void
	{
		$stream = File::openStream($this->filePath);

		static::assertInstanceOf(ResourceStream::class, $stream);
		static::assertTrue($stream->isReadable());
		static::assertTrue($stream->isSeekable());
		static::assertFalse($stream->isWriteable());

		$stream->close();
	}

	/**
	 * @noinspection PhpConditionAlreadyCheckedInspection
	 */
	public function invalidFopenFlagsProvider(): iterable
	{
		$readable = true;
		$writeable = true;
		$create = true;
		$append = true;
		$truncate = true;

		// append & truncate cannot both be true
		yield [$readable, $writeable, $create, $append, $truncate];
		yield [!$readable, $writeable, $create, $append, $truncate];
		yield [$readable, !$writeable, $create, $append, $truncate];
		yield [!$readable, !$writeable, $create, $append, $truncate];
		yield [$readable, $writeable, !$create, $append, $truncate];
		yield [!$readable, $writeable, !$create, $append, $truncate];
		yield [$readable, !$writeable, !$create, $append, $truncate];
		yield [!$readable, !$writeable, !$create, $append, $truncate];

		// if truncate is true, writeable and create must be true
		yield [$readable, $writeable, !$create, !$append, $truncate];
		yield [$readable, !$writeable, $create, !$append, $truncate];
		yield [$readable, !$writeable, !$create, !$append, $truncate];
		yield [!$readable, $writeable, !$create, !$append, $truncate];
		yield [!$readable, !$writeable, $create, !$append, $truncate];
		yield [!$readable, !$writeable, !$create, !$append, $truncate];

		// if append is true, writeable and create must be true
		yield [$readable, $writeable, !$create, $append, !$truncate];
		yield [$readable, !$writeable, $create, $append, !$truncate];
		yield [$readable, !$writeable, !$create, $append, !$truncate];
		yield [!$readable, $writeable, !$create, $append, !$truncate];
		yield [!$readable, !$writeable, $create, $append, !$truncate];
		yield [!$readable, !$writeable, !$create, $append, !$truncate];

		// if writable is false, create, append and truncate must be false
		yield [$readable, !$writeable, $create, !$append, $truncate];
		yield [$readable, !$writeable, !$create, !$append, $truncate];
		yield [$readable, !$writeable, !$create, $append, !$truncate];
		yield [$readable, !$writeable, $create, !$append, !$truncate];
	}

	/**
	 * @dataProvider invalidFopenFlagsProvider
	 *
	 * @param bool $read
	 * @param bool $write
	 * @param bool $create
	 * @param bool $append
	 * @param bool $truncate
	 */
	public function testInvalidFopenFlags(bool $read, bool $write, bool $create, bool $append, bool $truncate): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid combination of flags: readable=' . ($read ?: '0') . ', writeable=' . ($write ?: '0') . ', create=' . ($create ?: '0') . ', append=' . ($append ?: '0') . ', truncate=' . ($truncate ?: '0'));

		File::openStream($this->filePath, $read, $write, $create, $append, $truncate);
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

		File::openStream($fileMock);
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
			->expects('exists')
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

		File::openStream($fileMock, writeable: true);
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
			->expects('exists')
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

		File::openStream($fileMock, append: true);
	}

	public function testNonReadableParent(): void
	{
		$fileMock = M::mock(Contract\File::class);
		$directoryMock = M::mock(Contract\Directory::class);

		$fileMock
			->expects('isReadable')
			->withNoArgs()
			->andReturn(true)
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

		$fileMock
			->expects('getPath')
			->withNoArgs()
			->andReturn('/path/to/file')
		;

		$this->expectException(ReadonlyParentException::class);

		File::openStream($fileMock, create: true);
	}

	public function testFopenFailThrows(): void
	{
		$fileMock = M::mock(Contract\File::class);

		$fileMock
			->expects('isReadable')
			->withNoArgs()
			->andReturn(true)
		;

		$fileMock
			->expects('getPath')
			->withNoArgs()
			->andReturn('/path/to/file')
		;

		$this->expectException(RuntimeException::class);

		File::openStream($fileMock);
	}

	public function testPutContents(): void
	{
		$file = new File(tempnam(sys_get_temp_dir(), 'ele'));
		$streamMock = M::mock(Stream::class);

		$streamMock
			->expects('eof')
			->withNoArgs()
			->andReturn(false)
		;

		$streamMock
			->expects('read')
			->with(Contract\File::DEFAULT_STREAM_CHUNK_SIZE)
			->andReturn('hello world')
		;

		$streamMock
			->expects('eof')
			->withNoArgs()
			->andReturn(true)
		;

		$file->writeStream($streamMock);
		$file->delete();
	}

	public function testTouch(): void
	{
		$file = new File(Path::join(sys_get_temp_dir(), uniqid('ele', true) . '.tmp'));
		static::assertFalse($file->exists());

		$file->touch();
		static::assertTrue($file->exists());
		$file->touch();
		static::assertTrue($file->exists());

		$file->delete();
		static::assertFalse($file->exists());
		$file->touch();
		static::assertTrue($file->exists());

		$invalidFile = new File('/does/not/exist');
		static::assertFalse($invalidFile->exists());

		$this->expectException(FileNotCreatedException::class);
		$invalidFile->touch();
	}

	public function testMoveTo(): void
	{
		$file = new File(tempnam(sys_get_temp_dir(), 'ele'));

		$destinationFilename = new File(Path::join(sys_get_temp_dir(), uniqid('ele', true)));
		$destinationDirectory = new Directory(Path::join(sys_get_temp_dir(), 'movetest'));
		$destinationDirectoryFilename = new File(Path::join(sys_get_temp_dir(), 'movetest', $destinationFilename->getName()));

		$destinationDirectory->ensureExists();

		static::assertTrue($file->exists());
		static::assertFalse($destinationFilename->exists());
		$file->moveTo($destinationFilename);
		static::assertFalse($file->exists());
		static::assertTrue($destinationFilename->exists());
		static::assertFalse($destinationDirectoryFilename->exists());
		$destinationFilename->moveTo($destinationDirectory);
		static::assertTrue($destinationDirectoryFilename->exists());

		$nonExistentFile = new File('/i/dont/exist');
		$this->expectException(FileNotFoundException::class);
		$nonExistentFile->moveTo($destinationDirectoryFilename);
	}

	public function testCopyTo(): void
	{
		$file = new File(tempnam(sys_get_temp_dir(), 'ele'));

		$destinationFilename = new File(Path::join(sys_get_temp_dir(), uniqid('ele', true)));
		$destinationDirectory = new Directory(Path::join(sys_get_temp_dir(), 'movetest'));
		$destinationDirectoryFilename = new File(Path::join(sys_get_temp_dir(), 'movetest', $destinationFilename->getName()));

		$destinationDirectory->ensureExists();

		static::assertTrue($file->exists());
		static::assertFalse($destinationFilename->exists());
		$file->copyTo($destinationFilename);
		static::assertTrue($file->exists());
		static::assertTrue($destinationFilename->exists());
		static::assertFalse($destinationDirectoryFilename->exists());
		$destinationFilename->copyTo($destinationDirectory);
		static::assertTrue($destinationDirectoryFilename->exists());

		$nonExistentFile = new File('/i/dont/exist');
		$this->expectException(FileNotFoundException::class);
		$nonExistentFile->copyTo($destinationDirectoryFilename);
	}

	public function testDeleteNonExistent(): void
	{
		$file = new File(tempnam(sys_get_temp_dir(), 'ele'));
		$file->delete();

		static::assertFalse($file->exists());

		$this->expectException(FileNotFoundException::class);
		$file->delete();
	}
}
