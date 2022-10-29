<?php
declare(strict_types=1);

namespace Elephox\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\UploadError
 *
 * @internal
 */
class UploadErrorTest extends TestCase
{
	public function messageProvider(): iterable
	{
		yield [UploadError::Ok, 'There is no error, the file uploaded with success.'];
		yield [UploadError::IniSize, 'The uploaded file exceeds the upload_max_filesize directive in php.ini.'];
		yield [UploadError::FormSize, 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.'];
		yield [UploadError::Partial, 'The uploaded file was only partially uploaded.'];
		yield [UploadError::NoFile, 'No file was uploaded.'];
		yield [UploadError::NoTmpDir, 'Missing a temporary folder.'];
		yield [UploadError::CantWrite, 'Failed to write file to disk.'];
		yield [UploadError::Extension, 'A PHP extension stopped the file upload.'];
	}

	/**
	 * @dataProvider messageProvider
	 *
	 * @param UploadError $error
	 * @param string $message
	 */
	public function testGetMessage(UploadError $error, string $message): void
	{
		static::assertSame($message, $error->getMessage());
	}
}
