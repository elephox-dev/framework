<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Files\File;
use Elephox\Mimey\MimeType;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\UploadedFile
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Mimey\MimeType
 * @covers \Elephox\Http\UploadError
 * @covers \Elephox\Files\AbstractFilesystemNode
 * @covers \Elephox\Files\Directory
 * @covers \Elephox\Files\File
 * @covers \Elephox\Stream\ResourceStream
 *
 * @internal
 */
class UploadedFileTest extends TestCase
{
	public function testConstructor(): void
	{
		$tmp = tempnam(sys_get_temp_dir(), 'php');
		if (!$tmp) {
			throw new Exception('Failed to create tmp file');
		}
		$tmpFile = new File($tmp);
		$tmpFile->writeContents('test');

		try {
			$file = new UploadedFile('client name', 'client path', $tmpFile, MimeType::TextPlain, 123, UploadError::Ok);
			static::assertSame('client name', $file->getClientFilename());
			static::assertSame('client path', $file->getClientPath());
			static::assertSame('test', $file->getStream()->getContents());
			static::assertSame(123, $file->getSize());
			static::assertSame(MimeType::TextPlain, $file->getClientMimeType());
			static::assertSame(UploadError::Ok, $file->getUploadError());
		} finally {
			$tmpFile->delete();
		}
	}
}
