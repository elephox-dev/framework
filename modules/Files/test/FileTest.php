<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Files\Contract\Directory;
use Elephox\Mimey\MimeType;
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
 */
class FileTest extends MockeryTestCase
{
	/** @var resource $fileHandle */
	private $fileHandle;
	private string $filePath;
	private const FileContents = "This is a generated test file. You are free to delete it.";

	public function setUp(): void
	{
		parent::setUp();

		$this->fileHandle = tmpfile();
		if ($this->fileHandle === false) {
			throw new RuntimeException("Could not create temporary file.");
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
		self::assertEquals('txt', $file->getExtension());
	}

	public function testGetModifiedTime(): void
	{
		$file = new File($this->filePath);
		self::assertEquals(filemtime($this->filePath), $file->getModifiedTime()->getTimestamp());
	}

	public function testFileNotFoundModifiedTime(): void
	{
		$file = new File("/non-existent-file.txt");

		$this->expectException(FileNotFoundException::class);

		$file->getModifiedTime();
	}

	public function testFileNotFoundSize(): void
	{
		$file = new File("/non-existent-file.txt");

		$this->expectException(FileNotFoundException::class);

		$file->getSize();
	}

	public function testFileNotFoundHash(): void
	{
		$file = new File("/non-existent-file.txt");

		$this->expectException(FileNotFoundException::class);

		$file->getHash();
	}

	public function testGetPath(): void
	{
		$file = new File('/tmp/test.txt');
		self::assertEquals('/tmp/test.txt', $file->getPath());
	}

	public function testGetMimeType(): void
	{
		$file = new File($this->filePath);
		self::assertNull($file->getMimeType());

		$fileWithType = new File($this->filePath, MimeType::TextPlain);
		self::assertEquals(MimeType::TextPlain, $fileWithType->getMimeType());
	}

	public function testGetHash(): void
	{
		$file = new File($this->filePath);
		self::assertEquals(md5(self::FileContents), $file->getHash());
	}

	public function testGetSize(): void
	{
		$file = new File($this->filePath);
		self::assertEquals(strlen(self::FileContents), $file->getSize());
	}

	public function testGetParent(): void
	{
		$file = new File('/tmp/nested/deep/file/test.txt');
		$dir = $file->getParent();
		self::assertInstanceOf(Directory::class, $dir);
		self::assertEquals('/tmp/nested/deep/file', $dir->getPath());

		$upperDir = $file->getParent(2);
		self::assertInstanceOf(Directory::class, $upperDir);
		self::assertEquals('/tmp/nested/deep', $upperDir->getPath());

		$this->expectException(InvalidParentLevelException::class);
		$file->getParent(0);
	}

	public function testGetName(): void
	{
		$file = new File('/tmp/test.txt');
		self::assertEquals('test.txt', $file->getName());
	}

	public function testIsExecutable(): void
	{
		$file = new File($this->filePath);
		self::assertFalse($file->isExecutable());
	}

	public function testMoveTo(): void
	{
		$oldName = tempnam(sys_get_temp_dir(), 'test');
		$newName = new File($oldName . '.new');

		$file = new File($oldName);
		$file->moveTo($newName);

		self::assertFileExists($newName->getPath());
	}

	public function testOpenStream(): void
	{
		$stream = File::openStream($this->filePath);

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
			->andReturn(false);

		$fileMock
			->expects('getPath')
			->withNoArgs()
			->andReturn('/path/to/file');

		$this->expectException(UnreadableFileException::class);

		File::openStream($fileMock);
	}

	public function testNonWriteableFile(): void
	{
		$fileMock = M::mock(File::class);

		$fileMock
			->expects('isReadable')
			->withNoArgs()
			->andReturn(true);

		$fileMock
			->expects('isWritable')
			->withNoArgs()
			->andReturn(false);

		$fileMock
			->expects('getPath')
			->withNoArgs()
			->andReturn('/path/to/file');

		$this->expectException(ReadOnlyFileException::class);

		File::openStream($fileMock, writeable: true);
	}

	public function testNonWriteableFileAppend(): void
	{
		$fileMock = M::mock(File::class);

		$fileMock
			->expects('isReadable')
			->withNoArgs()
			->andReturn(true);

		$fileMock
			->expects('isWritable')
			->withNoArgs()
			->andReturn(false);

		$fileMock
			->expects('getPath')
			->withNoArgs()
			->andReturn('/path/to/file');

		$this->expectException(ReadOnlyFileException::class);

		File::openStream($fileMock, append: true);
	}
}
