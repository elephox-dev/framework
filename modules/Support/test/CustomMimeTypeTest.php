<?php
declare(strict_types=1);

namespace Elephox\Support;

use Elephox\Mimey\MimeType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Elephox\Support\CustomMimeType
 *
 * @internal
 */
class CustomMimeTypeTest extends TestCase
{
	public function testInstantiate(): void
	{
		$mimeType = CustomMimeType::from('image/png', 'png');
		$builtIn = MimeType::ImagePng;

		static::assertInstanceOf(CustomMimeType::class, $mimeType);
		static::assertSame($builtIn->getValue(), $mimeType->getValue());
		static::assertSame($builtIn->getExtension(), $mimeType->getExtension());
	}

	/**
	 * @
	 */
	public function testFromFileString(): void
	{
		$result = CustomMimeType::fromFile('test.dat');
		$builtIn = MimeType::ApplicationOctetStream;

		static::assertInstanceOf(CustomMimeType::class, $result);
		static::assertSame($builtIn->getValue(), $result->getValue());
		static::assertSame('dat', $result->getExtension());
	}

	public function testFromFileResource(): void
	{
		$f = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'elephox-mime-test.txt';
		$res = false;

		try {
			file_put_contents($f, 'Test data');
			$res = fopen($f, 'rb');
			if ($res === false) {
				static::markTestSkipped('Failed to create file resource');
			}

			$mimeType = CustomMimeType::fromFile($res);
			$builtIn = MimeType::TextPlain;

			static::assertInstanceOf(MimeType::class, $mimeType);
			static::assertSame($builtIn->getValue(), $mimeType->getValue());
			static::assertSame($builtIn->getExtension(), $mimeType->getExtension());
		} finally {
			if (is_resource($res)) {
				fclose($res);
			}

			if (file_exists($f)) {
				unlink($f);
			}
		}
	}

	public function testFromFilename(): void
	{
		$pngMimeType = CustomMimeType::fromFilename('test.png');

		static::assertSame(MimeType::ImagePng, $pngMimeType);
		static::assertSame('image/png', $pngMimeType->getValue());
		static::assertSame('png', $pngMimeType->getExtension());

		$unknownMimeType = CustomMimeType::fromFilename('test.unknown');

		static::assertInstanceOf(CustomMimeType::class, $unknownMimeType);
		static::assertSame('application/octet-stream', $unknownMimeType->getValue());
		static::assertSame('unknown', $unknownMimeType->getExtension());
	}

	public function testFromEmptyFilename(): void
	{
		$empty = CustomMimeType::fromFilename('no-ext');
		$builtIn = MimeType::ApplicationOctetStream;

		static::assertInstanceOf(CustomMimeType::class, $empty);
		static::assertSame($builtIn->getValue(), $empty->getValue());
		static::assertSame('', $empty->getExtension());
	}

	public function testFromFileInvalidType(): void
	{
		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Unable to determine mime type of file');

		CustomMimeType::fromFile(123);
	}

	public function testFromFileWithFilename(): void
	{
		$mimeType = CustomMimeType::fromFile('test.png');

		static::assertSame(MimeType::ImagePng, $mimeType);
	}
}
