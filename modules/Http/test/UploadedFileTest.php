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
 *
 * @internal
 */
class UploadedFileTest extends TestCase
{
	public function testConstructor(): void
	{
		$file = new UploadedFile('client name', 'client path', new StringStream('contents'), MimeType::TextPlain, 123, UploadError::Ok);
		static::assertEquals('client name', $file->getClientFilename());
		static::assertEquals('client path', $file->getClientPath());
		static::assertEquals('contents', $file->getStream()->getContents());
		static::assertEquals(123, $file->getSize());
		static::assertEquals(MimeType::TextPlain, $file->getClientMimeType());
		static::assertEquals(UploadError::Ok, $file->getError());
	}
}
