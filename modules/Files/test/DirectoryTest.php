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
 * @covers \Elephox\Files\DirectoryNotEmptyException
 * @covers \Elephox\Files\FileNotFoundException
 * @covers \Elephox\Files\FileException
 * @covers \Elephox\Collection\Iterator\SelectIterator
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\Files\FilesystemNodeNotFoundException
 * @covers \Elephox\Files\UnknownFilesystemNode
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
		$nonExistentChild = $emptyDir->getChild("test123");
		self::assertFalse($nonExistentChild->exists());
		self::assertInstanceOf(UnknownFilesystemNode::class, $nonExistentChild);

		$this->expectException(FilesystemNodeNotFoundException::class);
		$directory->getChild("test123", true);
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
	}

	public function testGetDirectory(): void
	{
		$directory = new Directory($this->dirPath);
		self::assertEquals($this->dirPath . DIRECTORY_SEPARATOR . "test", $directory->getDirectory("test")->getPath());
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

	public function testIsRoot(): void
	{
		self::assertFalse((new Directory("/long/path/to/test"))->isRoot());
		self::assertTrue((new Directory("/"))->isRoot());
		self::assertFalse((new Directory("C:\\Windows\\System32"))->isRoot());
		self::assertTrue((new Directory("C:\\"))->isRoot());
	}

	public function testEnsureExists(): void
	{
		$directory = new Directory(Path::join(sys_get_temp_dir(), "testdir"));

		self::assertFalse($directory->exists());
		$directory->ensureExists();
		self::assertTrue($directory->exists());

		$directory->delete();
	}

	public function testDelete(): void
	{
		$testDir1 = Path::join(sys_get_temp_dir(), "testdir1");
		$testDir2 = Path::join(sys_get_temp_dir(), "testdir2");
		$testDir3 = Path::join(sys_get_temp_dir(), "testdir2", "testdir3");
		@mkdir($testDir1, recursive: true);
		@mkdir($testDir3, recursive: true);

		$dir1 = new Directory($testDir1);
		self::assertTrue($dir1->exists());
		$dir1->delete(false);

		$dir2 = new Directory($testDir2);
		$dir3 = new Directory($testDir3);
		self::assertTrue($dir2->exists());
		self::assertTrue($dir3->exists());
		$dir2->delete(true);

		self::assertFalse($dir3->exists());
		self::assertFalse($dir2->exists());

		@mkdir($testDir3, recursive: true);
		$this->expectException(DirectoryNotEmptyException::class);
		$dir2->delete(false);
	}
}
