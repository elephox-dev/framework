<?php
declare(strict_types=1);

namespace Elephox\Http\PSR7;

use Elephox\Files\Directory;
use Elephox\Files\File;
use Elephox\Files\Path;
use Elephox\Http\UploadedFile;
use Http\Psr7Test\UploadedFileIntegrationTest;
use Throwable;

/**
 * @covers \Elephox\Http\UploadedFile
 * @covers \Elephox\Files\AbstractFilesystemNode
 * @covers \Elephox\Files\Directory
 * @covers \Elephox\Files\File
 * @covers \Elephox\Files\Path
 * @covers \Elephox\Stream\ResourceStream
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Files\FileException
 * @covers \Elephox\Files\FileNotFoundException
 * @covers \Elephox\Files\FilesystemNodeNotFoundException
 * @covers \Elephox\Files\UnreadableFileException
 *
 * @internal
 */
class UploadedFileTest extends UploadedFileIntegrationTest
{
	private static function getUploadedFileTestTempDir(): Directory
	{
		return Directory::from(Path::join(getcwd(), 'tmp', 'tests', 'uploadedFiles'));
	}

	public static function tearDownAfterClass(): void
	{
		parent::tearDownAfterClass();

		try {
			self::getUploadedFileTestTempDir()->delete();
		} catch (Throwable) { /* ignore */
		}
	}

	public function createSubject(): UploadedFile
	{
		$file = File::temp(self::getUploadedFileTestTempDir());
		$file->writeContents('Foo Bar');

		return new UploadedFile('filename.txt', 'C:\\Users\\Foo\\Downloads', $file);
	}
}
