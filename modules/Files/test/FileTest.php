<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Support\MimeType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Files\File
 * @covers \Elephox\Files\Directory
 * @covers \Elephox\Support\MimeType
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Files\InvalidParentLevelException
 * @covers \Elephox\Files\FileException
 * @covers \Elephox\Files\FileNotFoundException
 */
class FileTest extends TestCase
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

		$fileWithType = new File($this->filePath, MimeType::Textplain);
		self::assertEquals(MimeType::Textplain, $fileWithType->getMimeType());
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
}
