<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Files\File;
use Elephox\Mimey\MimeType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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
final class UploadedFileTest extends TestCase
{
	/**
	 * @throws RuntimeException
	 */
	public function testConstructor(): void
	{
		$tmp = tempnam(sys_get_temp_dir(), 'php');
		if (!$tmp) {
			throw new RuntimeException('Failed to create tmp file');
		}
		$tmpFile = new File($tmp);
		$tmpFile->writeContents('test');

		try {
			$file = new UploadedFile('client name', 'client path', $tmpFile, MimeType::TextPlain, 123, UploadError::Ok);
			self::assertSame('client name', $file->getClientFilename());
			self::assertSame('client path', $file->getClientPath());
			self::assertSame('test', $file->getStream()->getContents());
			self::assertSame(123, $file->getSize());
			self::assertSame(MimeType::TextPlain, $file->getClientMimeType());
			self::assertSame(UploadError::Ok, $file->getUploadError());
		} finally {
			$tmpFile->delete();
		}
	}
}
