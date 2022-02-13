<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Mimey\MimeType;
use Elephox\Stream\StringStream;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\UploadedFile
 * @covers \Elephox\Stream\StringStream
 * @covers \Elephox\Mimey\MimeType
 * @covers \Elephox\Http\UploadError
 */
class UploadedFileTest extends TestCase
{
	public function testConstructor(): void
	{
		$file = new UploadedFile("client name", "client path", new StringStream("contents"), MimeType::TextPlain, 123, UploadError::Ok);
		self::assertEquals("client name", $file->getClientFilename());
		self::assertEquals("client path", $file->getClientPath());
		self::assertEquals("contents", $file->getStream()->getContents());
		self::assertEquals(123, $file->getSize());
		self::assertEquals(MimeType::TextPlain, $file->getClientMimeType());
		self::assertEquals(UploadError::Ok, $file->getError());
	}
}
