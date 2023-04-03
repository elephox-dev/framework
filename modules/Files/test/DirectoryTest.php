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
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\Files\FilesystemNodeNotFoundException
 * @covers \Elephox\Files\DirectoryCouldNotBeScannedException
 * @covers \Elephox\Files\UnknownFilesystemNode
 * @covers \Elephox\Files\AbstractFilesystemNode
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @internal
 */
class DirectoryTest extends TestCase
{
	/**
	 * @var resource $fileHandle
	 */
	private $fileHandle;
	private string $filePath;
	private string $dirPath;
	private string $emptyDirPath;
	private string $nonEmptyDirPath;
	private const FileContents = 'This is a generated test file. You are free to delete it.';

	function rrmdir(string $dir): void {
		if (!is_dir($dir)) {
			return;
		}

		$objects = scandir($dir) or throw new RuntimeException("Unable to scan dir $dir");
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
					$this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
				else
					unlink($dir. DIRECTORY_SEPARATOR .$object);
			}
		}

		rmdir($dir);
	}

	public function setUp(): void
	{
		parent::setUp();

		$this->dirPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'elephox-test';

		$this->emptyDirPath = $this->dirPath . DIRECTORY_SEPARATOR . 'empty';
		if (is_dir($this->emptyDirPath)) {
			$this->rrmdir($this->emptyDirPath);
		}

		mkdir($this->emptyDirPath, recursive: true);

		$this->nonEmptyDirPath = $this->dirPath . DIRECTORY_SEPARATOR . 'nonEmpty';
		if (is_dir($this->nonEmptyDirPath)) {
			$this->rrmdir($this->nonEmptyDirPath);
		}

		mkdir($this->nonEmptyDirPath);

		$this->filePath = $this->nonEmptyDirPath . DIRECTORY_SEPARATOR. 'testfile';
		$this->fileHandle = fopen($this->filePath, 'wb+');

		mkdir($this->nonEmptyDirPath . DIRECTORY_SEPARATOR . 'testfolder');
		touch($this->nonEmptyDirPath . DIRECTORY_SEPARATOR . 'testfolder' . DIRECTORY_SEPARATOR. 'testfile2');

		fwrite($this->fileHandle, self::FileContents);
	}

	public function tearDown(): void
	{
		parent::tearDown();

		fclose($this->fileHandle);
		$this->rrmdir($this->dirPath);
	}

	public function testGetPath(): void
	{
		$directory = new Directory('/test/path');
		static::assertSame('/test/path', $directory->path());
	}

	public function testGetPathWithTrailingSlash(): void
	{
		$directory = new Directory('/test/path/');
		static::assertSame('/test/path/', $directory->path());
	}

	public function testToString(): void
	{
		$directory = new Directory('/test/path');
		static::assertSame('/test/path', (string) $directory);
	}

	public function testGetChild(): void
	{
		$directory = new Directory($this->nonEmptyDirPath);

		$fileChild = $directory->child(pathinfo($this->filePath, PATHINFO_BASENAME));
		static::assertInstanceOf(File::class, $fileChild);
		static::assertSame($this->filePath, $fileChild->path());

		$dirChild = $directory->child('testfolder');
		static::assertInstanceOf(Directory::class, $dirChild);
		static::assertSame($this->nonEmptyDirPath . DIRECTORY_SEPARATOR . 'testfolder', $dirChild->path());

		$emptyDir = new Directory($this->dirPath . DIRECTORY_SEPARATOR . 'test');
		$nonExistentChild = $emptyDir->child('test123');
		static::assertFalse($nonExistentChild->exists());
		static::assertInstanceOf(UnknownFilesystemNode::class, $nonExistentChild);

		// TODO: add test for symlink

		$this->expectException(FilesystemNodeNotFoundException::class);
		$directory->child('test123', true);
	}

	public function testIsEmpty(): void
	{
		$directory = new Directory($this->emptyDirPath);
		static::assertTrue($directory->isEmpty());
	}

	public function testExistsIsFalseOnFiles(): void
	{
		$directory = new Directory($this->filePath);
		static::assertFalse($directory->exists());
	}

	public function testGetModifiedTime(): void
	{
		$directory = new Directory($this->nonEmptyDirPath);
		static::assertSame(filemtime($this->filePath), $directory->modifiedAt()->getTimestamp());
	}

	public function testGetModifiedTimeNonExistent(): void
	{
		$directory = new Directory('/test/path');

		$this->expectException(FilesystemNodeNotFoundException::class);
		$this->expectExceptionMessage('Filesystem node at /test/path not found');

		$directory->modifiedAt();
	}

	public function testGetChildren(): void
	{
		$nonEmptyDirectory = new Directory($this->nonEmptyDirPath);
		$children = $nonEmptyDirectory->children();
		static::assertNotEmpty($children);
		static::assertContainsOnlyInstancesOf(FilesystemNode::class, $children);

		$emptyDirectory = new Directory($this->emptyDirPath);
		static::assertEmpty($emptyDirectory->children());
	}

	public function testRecurseChildren(): void
	{
		$directory = new Directory($this->nonEmptyDirPath);
		$children = $directory->recurseChildren(true);
		static::assertNotEmpty($children);
		static::assertContainsOnlyInstancesOf(FilesystemNode::class, $children);

		$testDirectory = new Directory($this->emptyDirPath);
		static::assertEmpty($testDirectory->recurseChildren());
	}

	public function testNonExistentGetChildren(): void
	{
		$directory = new Directory('/test/path');

		$this->expectException(DirectoryNotFoundException::class);
		$directory->children();
	}

	public function testGetParent(): void
	{
		$directory = new Directory('/long/path/to/test');
		static::assertSame('/long/path/to', $directory->parent()->path());

		$this->expectException(InvalidParentLevelException::class);
		$directory->parent(0);
	}

	public function testGetFiles(): void
	{
		$nonEmptyDirectory = new Directory($this->nonEmptyDirPath);
		$files = $nonEmptyDirectory->files();
		static::assertNotEmpty($files);
		static::assertContainsOnlyInstancesOf(File::class, $files);

		$emptyDirectory = new Directory($this->emptyDirPath);
		static::assertEmpty($emptyDirectory->files());
	}

	public function testGetFile(): void
	{
		$directory = new Directory($this->nonEmptyDirPath);
		static::assertSame($this->filePath, $directory->file(pathinfo($this->filePath, PATHINFO_BASENAME))->path());
	}

	public function testGetDirectory(): void
	{
		$directory = new Directory($this->nonEmptyDirPath);
		static::assertSame($this->nonEmptyDirPath . DIRECTORY_SEPARATOR . 'testfolder', $directory->directory('testfolder')->path());
	}

	public function testGetDirectories(): void
	{
		$nonEmptyDirectory = new Directory($this->nonEmptyDirPath);
		$dirs = $nonEmptyDirectory->directories();
		static::assertNotEmpty($dirs);
		static::assertContainsOnlyInstancesOf(Directory::class, $dirs);

		$emptyDirectory = new Directory($this->emptyDirPath);
		static::assertEmpty($emptyDirectory->directories());
	}

	public function testGetName(): void
	{
		$directory = new Directory('/path/test');
		static::assertSame('test', $directory->name());
	}

	public function testIsRoot(): void
	{
		static::assertFalse((new Directory('/long/path/to/test'))->isRoot());
		static::assertTrue((new Directory('/'))->isRoot());
		static::assertFalse((new Directory('C:\\Windows\\System32'))->isRoot());
		static::assertTrue((new Directory('C:\\'))->isRoot());
	}

	public function testEnsureExists(): void
	{
		$directory = new Directory(Path::join(sys_get_temp_dir(), 'testdir'));

		try {
			static::assertFalse($directory->exists());
			$directory->ensureExists();
			static::assertTrue($directory->exists());
		} finally {
			$directory->delete();
		}
	}

	public function testDelete(): void
	{
		$testDir1 = Path::join(sys_get_temp_dir(), 'testdir1');
		$testDir2 = Path::join(sys_get_temp_dir(), 'testdir2');
		$testDir3 = Path::join($testDir2, 'testdir3');
		@mkdir($testDir1, recursive: true);
		@mkdir($testDir3, recursive: true);

		$dir1 = new Directory($testDir1);
		static::assertTrue($dir1->exists());
		$dir1->delete(false);

		$dir2 = new Directory($testDir2);
		$dir3 = new Directory($testDir3);
		static::assertTrue($dir2->exists());
		static::assertTrue($dir3->exists());
		$dir2->delete(true);

		static::assertFalse($dir3->exists());
		static::assertFalse($dir2->exists());

		@mkdir($testDir3, recursive: true);
		$this->expectException(DirectoryNotEmptyException::class);
		$dir2->delete(false);
	}
}
