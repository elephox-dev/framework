<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Files\Contract\FilesystemNode;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Files\Directory
 * @covers \Elephox\Files\File
 * @covers \Elephox\Files\Path
 * @covers \Elephox\Collection\ArrayList
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

		mkdir($this->dirPath . DIRECTORY_SEPARATOR . "test");
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
		$this->assertEquals("/test/path", $directory->getPath());
	}

	public function testGetChild(): void
	{
		$directory = new Directory($this->dirPath);

		$fileChild = $directory->getChild(pathinfo($this->filePath, PATHINFO_BASENAME));
		$this->assertInstanceOf(File::class, $fileChild);
		$this->assertEquals($this->filePath, $fileChild->getPath());

		$dirChild = $directory->getChild("test");
		$this->assertInstanceOf(Directory::class, $dirChild);
		$this->assertEquals($this->dirPath . DIRECTORY_SEPARATOR . "test", $dirChild->getPath());

		$emptyDir = new Directory($this->dirPath . DIRECTORY_SEPARATOR . "test");
		$this->assertNull($emptyDir->getChild("test"));
	}

	public function testIsEmpty(): void
	{
		$directory = new Directory($this->dirPath . DIRECTORY_SEPARATOR . "test");
		$this->assertTrue($directory->isEmpty());
	}

	public function testGetModifiedTime(): void
	{
		$directory = new Directory($this->dirPath);
		$this->assertEquals(filemtime($this->filePath), $directory->getModifiedTime()->getTimestamp());
	}

	public function testGetChildren(): void
	{
		$directory = new Directory($this->dirPath);
		$this->assertNotEmpty($directory->getChildren());
		$this->assertContainsOnlyInstancesOf(FilesystemNode::class, $directory->getChildren());

		$testDirectory = new Directory($this->dirPath . DIRECTORY_SEPARATOR . "test");
		$this->assertEmpty($testDirectory->getChildren());
	}

	public function testGetParent(): void
	{
		$directory = new Directory("/long/path/to/test");
		$this->assertEquals("/long/path/to", $directory->getParent()->getPath());

		$this->expectException(OutOfRangeException::class);
		$directory->getParent(0);
	}

	public function testGetFiles(): void
	{
		$directory = new Directory($this->dirPath);
		$this->assertNotEmpty($directory->getFiles());
		$this->assertContainsOnlyInstancesOf(File::class, $directory->getFiles());

		$testDirectory = new Directory($this->dirPath . DIRECTORY_SEPARATOR . "test");
		$this->assertEmpty($testDirectory->getFiles());
	}

	public function testIsRoot(): void
	{
		$directory = new Directory("/long/path/to/test");
		$this->assertFalse($directory->isRoot());

		$rootDirectory = new Directory("/");
		$this->assertTrue($rootDirectory->isRoot());
	}

	public function testGetFile(): void
	{
		$directory = new Directory($this->dirPath);
		$this->assertEquals($this->filePath, $directory->getFile(pathinfo($this->filePath, PATHINFO_BASENAME))->getPath());

		$this->assertNull($directory->getFile("non-existent-file"));
	}

	public function testGetDirectory(): void
	{
		$directory = new Directory($this->dirPath);
		$this->assertEquals($this->dirPath . DIRECTORY_SEPARATOR . "test", $directory->getDirectory("test")->getPath());

		$this->assertNull($directory->getDirectory("non-existent-dir"));
	}

	public function testGetDirectories(): void
	{
		$directory = new Directory($this->dirPath);
		$this->assertNotEmpty($directory->getDirectories());
		$this->assertContainsOnlyInstancesOf(Directory::class, $directory->getDirectories());

		$testDirectory = new Directory($this->dirPath . DIRECTORY_SEPARATOR . "test");
		$this->assertEmpty($testDirectory->getDirectories());
	}

	public function testGetName(): void
	{
		$directory = new Directory("/path/test");
		$this->assertEquals("test", $directory->getName());
	}
}
