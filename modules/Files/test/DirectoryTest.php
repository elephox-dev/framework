<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Files\Contract\FilesystemNode;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Files\Directory
 * @covers \Elephox\Files\File
 * @covers \Elephox\Files\Path
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Files\InvalidParentLevelException
 * @covers \Elephox\Files\DirectoryNotFoundException
 * @covers \Elephox\Files\FileNotFoundException
 * @covers \Elephox\Files\FileException
 */
class DirectoryTest extends TestCase
{
	/** @var resource $fileHandle */
	private $fileHandle;
	private string $filePath;
	private string $dirPath;
	private const FileContents = "This is a generated test file. You are free to delete it.";

	public function setUp(): void
	{
		parent::setUp();

		$this->fileHandle = tmpfile();
		if ($this->fileHandle === false) {
			throw new RuntimeException("Could not create temporary file.");
		}

		$this->filePath = stream_get_meta_data($this->fileHandle)['uri'];
		$this->dirPath = dirname($this->filePath);

		$emptyDir = $this->dirPath . DIRECTORY_SEPARATOR . "test";
		if (is_dir($emptyDir)) {
			rmdir($emptyDir);
		}

		mkdir($emptyDir);
		fwrite($this->fileHandle, self::FileContents);
	}

	public function tearDown(): void
	{
		parent::tearDown();

		fclose($this->fileHandle);
		rmdir($this->dirPath . DIRECTORY_SEPARATOR . "test");
	}

	public function testGetPath(): void
	{
		$directory = new Directory("/test/path");
		self::assertEquals("/test/path", $directory->getPath());
	}

	public function testGetChild(): void
	{
		$directory = new Directory($this->dirPath);

		$fileChild = $directory->getChild(pathinfo($this->filePath, PATHINFO_BASENAME));
		self::assertInstanceOf(File::class, $fileChild);
		self::assertEquals($this->filePath, $fileChild->getPath());

		$dirChild = $directory->getChild("test");
		self::assertInstanceOf(Directory::class, $dirChild);
		self::assertEquals($this->dirPath . DIRECTORY_SEPARATOR . "test", $dirChild->getPath());

		$emptyDir = new Directory($this->dirPath . DIRECTORY_SEPARATOR . "test");
		$this->expectException(FileNotFoundException::class);
		$emptyDir->getChild("test");
	}

	public function testIsEmpty(): void
	{
		$directory = new Directory($this->dirPath . DIRECTORY_SEPARATOR . "test");
		self::assertTrue($directory->isEmpty());
	}

	public function testGetModifiedTime(): void
	{
		$directory = new Directory($this->dirPath);
		self::assertEquals(filemtime($this->filePath), $directory->getModifiedTime()->getTimestamp());
	}

	public function testGetModifiedTimeNonExistent(): void
	{
		$directory = new Directory("/test/path");

		$this->expectException(DirectoryNotFoundException::class);
		$directory->getModifiedTime();
	}

	public function testGetChildren(): void
	{
		$directory = new Directory($this->dirPath);
		$children = $directory->getChildren();
		self::assertNotEmpty($children);
		self::assertContainsOnlyInstancesOf(FilesystemNode::class, $children);

		$testDirectory = new Directory($this->dirPath . DIRECTORY_SEPARATOR . "test");
		self::assertEmpty($testDirectory->getChildren());
	}

	public function testNonExistentGetChildren(): void
	{
		$directory = new Directory("/test/path");

		$this->expectException(DirectoryNotFoundException::class);
		$directory->getChildren();
	}

	public function testGetParent(): void
	{
		$directory = new Directory("/long/path/to/test");
		self::assertEquals("/long/path/to", $directory->getParent()->getPath());

		$this->expectException(InvalidParentLevelException::class);
		$directory->getParent(0);
	}

	public function testGetFiles(): void
	{
		$directory = new Directory($this->dirPath);
		$files = $directory->getFiles();
		self::assertNotEmpty($files);
		self::assertContainsOnlyInstancesOf(File::class, $files);

		$testDirectory = new Directory($this->dirPath . DIRECTORY_SEPARATOR . "test");
		self::assertEmpty($testDirectory->getFiles());
	}

	public function testGetFile(): void
	{
		$directory = new Directory($this->dirPath);
		self::assertEquals($this->filePath, $directory->getFile(pathinfo($this->filePath, PATHINFO_BASENAME))->getPath());

		$this->expectException(FileNotFoundException::class);
		$directory->getFile("non-existent-file");
	}

	public function testGetDirectory(): void
	{
		$directory = new Directory($this->dirPath);
		self::assertEquals($this->dirPath . DIRECTORY_SEPARATOR . "test", $directory->getDirectory("test")->getPath());

		$this->expectException(DirectoryNotFoundException::class);
		$directory->getDirectory("non-existent-dir");
	}

	public function testGetDirectories(): void
	{
		$directory = new Directory($this->dirPath);
		$dirs = $directory->getDirectories();
		self::assertNotEmpty($dirs);
		self::assertContainsOnlyInstancesOf(Directory::class, $dirs);

		$testDirectory = new Directory($this->dirPath . DIRECTORY_SEPARATOR . "test");
		self::assertEmpty($testDirectory->getDirectories());
	}

	public function testGetName(): void
	{
		$directory = new Directory("/path/test");
		self::assertEquals("test", $directory->getName());
	}
}
