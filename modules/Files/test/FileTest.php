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
final class FileTest extends MockeryTestCase
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
		self::assertSame('txt', $file->extension());
	}

	public function testGetNameWithoutExtension(): void
	{
		$file = new File('/tmp/test.txt');
		self::assertSame('test', $file->getNameWithoutExtension());
	}

	public function testToString(): void
	{
		$file = new File('/tmp/test.txt');
		self::assertSame('/tmp/test.txt', (string) $file);
	}

	public function testGetModifiedTime(): void
	{
		$file = new File($this->filePath);
		self::assertSame(filemtime($this->filePath), $file->modifiedAt()->getTimestamp());
	}

	public function testFileNotFoundModifiedTime(): void
	{
		$file = new File('/non-existent-file.txt');

		$this->expectException(FilesystemNodeNotFoundException::class);
		$this->expectExceptionMessage('Filesystem node at /non-existent-file.txt not found');

		$file->modifiedAt();
	}

	public function testFileNotFoundSize(): void
	{
		$file = new File('/non-existent-file.txt');

		$this->expectException(FileNotFoundException::class);

		$file->size();
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
		self::assertSame('/tmp/test.txt', $file->path());
	}

	public function testGetMimeType(): void
	{
		$file = new File($this->filePath);
		self::assertNull($file->mimeType());

		$fileWithType = new File($this->filePath, MimeType::TextPlain);
		self::assertSame(MimeType::TextPlain, $fileWithType->mimeType());
	}

	public function testGetHash(): void
	{
		$file = new File($this->filePath);
		self::assertSame(md5(self::FileContents), $file->getHash());
	}

	public function testGetSize(): void
	{
		$file = new File($this->filePath);
		self::assertSame(strlen(self::FileContents), $file->size());
	}

	public function testGetParent(): void
	{
		$file = new File('/tmp/nested/deep/file/test.txt');
		$dir = $file->parent();
		self::assertInstanceOf(Directory::class, $dir);
		self::assertSame('/tmp/nested/deep/file', $dir->path());

		$upperDir = $file->parent(2);
		self::assertInstanceOf(Directory::class, $upperDir);
		self::assertSame('/tmp/nested/deep', $upperDir->path());

		$this->expectException(InvalidParentLevelException::class);
		$file->parent(0);
	}

	public function testGetName(): void
	{
		$file = new File('/tmp/test.txt');
		self::assertSame('test.txt', $file->name());
	}

	public function testIsExecutable(): void
	{
		$file = new File($this->filePath);
		self::assertFalse($file->isExecutable());
	}

	public function testOpenStream(): void
	{
		$stream = File::openStream($this->filePath);

		self::assertInstanceOf(ResourceStream::class, $stream);
		self::assertTrue($stream->isReadable());
		self::assertTrue($stream->isSeekable());
		self::assertFalse($stream->isWritable());

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
			->expects('path')
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
			->expects('path')
			->withNoArgs()
			->andReturn('/path/to/file')
		;

		$this->expectException(ReadOnlyFileException::class);

		File::openStream($fileMock, writable: true);
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
			->expects('path')
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
			->expects('parent')
			->withNoArgs()
			->andReturn($directoryMock)
		;

		$directoryMock
			->expects('ensureExists')
			->withNoArgs()
			->andReturns()
		;

		$directoryMock
			->expects('isReadonly')
			->withNoArgs()
			->andReturn(true)
		;

		$fileMock
			->expects('path')
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
			->expects('path')
			->withNoArgs()
			->andReturn('/path/to/file')
		;

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('[2] fopen(/path/to/file): Failed to open stream: No such file or directory in ');

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
		$file = File::temp();
		self::assertFalse($file->exists());

		$file->touch();
		self::assertTrue($file->exists());
		$file->touch();
		self::assertTrue($file->exists());

		$file->delete();
		self::assertFalse($file->exists());
		$file->touch();
		self::assertTrue($file->exists());

		$invalidFile = new File('');
		self::assertFalse($invalidFile->exists());

		$this->expectException(FileNotCreatedException::class);
		$invalidFile->touch();
	}

	public function testExistsIsFalseOnDirectories(): void
	{
		$file = new File(sys_get_temp_dir());
		self::assertFalse($file->exists());
	}

	public function testMoveTo(): void
	{
		$file = new File(tempnam(sys_get_temp_dir(), 'ele'));

		$destinationFilename = new File(Path::join(sys_get_temp_dir(), uniqid('ele', true)));
		$destinationDirectory = new Directory(Path::join(sys_get_temp_dir(), 'movetest'));
		$destinationDirectoryFilename = new File(Path::join(sys_get_temp_dir(), 'movetest', $destinationFilename->name()));

		$destinationDirectory->ensureExists();

		self::assertTrue($file->exists());
		self::assertFalse($destinationFilename->exists());
		$file->moveTo($destinationFilename);
		self::assertFalse($file->exists());
		self::assertTrue($destinationFilename->exists());
		self::assertFalse($destinationDirectoryFilename->exists());
		$destinationFilename->moveTo($destinationDirectory);
		self::assertTrue($destinationDirectoryFilename->exists());

		$nonExistentFile = new File('/i/dont/exist');
		$this->expectException(FileNotFoundException::class);
		$nonExistentFile->moveTo($destinationDirectoryFilename);
	}

	public function testCopyTo(): void
	{
		$file = new File(tempnam(sys_get_temp_dir(), 'ele'));

		$destinationFilename = new File(Path::join(sys_get_temp_dir(), uniqid('ele', true)));
		$destinationDirectory = new Directory(Path::join(sys_get_temp_dir(), 'movetest'));
		$destinationDirectoryFilename = new File(Path::join(sys_get_temp_dir(), 'movetest', $destinationFilename->name()));

		$destinationDirectory->ensureExists();

		self::assertTrue($file->exists());
		self::assertFalse($destinationFilename->exists());
		$file->copyTo($destinationFilename);
		self::assertTrue($file->exists());
		self::assertTrue($destinationFilename->exists());
		self::assertFalse($destinationDirectoryFilename->exists());
		$destinationFilename->copyTo($destinationDirectory);
		self::assertTrue($destinationDirectoryFilename->exists());

		$nonExistentFile = new File('/i/dont/exist');
		$this->expectException(FileNotFoundException::class);
		$nonExistentFile->copyTo($destinationDirectoryFilename);
	}

	public function testDeleteNonExistent(): void
	{
		$file = new File('/path/to/non/existent/file');

		self::assertFalse($file->exists());

		$this->expectException(FileNotFoundException::class);
		$this->expectExceptionMessage('File at /path/to/non/existent/file not found');

		$file->delete();
	}

	public function testIsWritableThrowsIfNotExists(): void
	{
		$file = new File('/path/to/non/existent/file');

		self::assertFalse($file->exists());

		$this->expectException(FileNotFoundException::class);
		$this->expectExceptionMessage('File at /path/to/non/existent/file not found');

		$file->isWritable();
	}
}
