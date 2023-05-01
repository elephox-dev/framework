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
final class CustomMimeTypeTest extends TestCase
{
	public function testInstantiate(): void
	{
		$mimeType = CustomMimeType::from('image/png', 'png');
		$builtIn = MimeType::ImagePng;

		self::assertInstanceOf(CustomMimeType::class, $mimeType);
		self::assertSame($builtIn->getValue(), $mimeType->getValue());
		self::assertSame($builtIn->getExtension(), $mimeType->getExtension());
	}

	/**
	 * @
	 */
	public function testFromFileString(): void
	{
		$result = CustomMimeType::fromFile('test.dat');
		$builtIn = MimeType::ApplicationOctetStream;

		self::assertInstanceOf(CustomMimeType::class, $result);
		self::assertSame($builtIn->getValue(), $result->getValue());
		self::assertSame('dat', $result->getExtension());
	}

	public function testFromFileResource(): void
	{
		$f = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'elephox-mime-test.txt';
		$res = false;

		try {
			file_put_contents($f, 'Test data');
			$res = fopen($f, 'rb');
			if ($res === false) {
				self::markTestSkipped('Failed to create file resource');
			}

			$mimeType = CustomMimeType::fromFile($res);
			$builtIn = MimeType::TextPlain;

			self::assertInstanceOf(MimeType::class, $mimeType);
			self::assertSame($builtIn->getValue(), $mimeType->getValue());
			self::assertSame($builtIn->getExtension(), $mimeType->getExtension());
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

		self::assertSame(MimeType::ImagePng, $pngMimeType);
		self::assertSame('image/png', $pngMimeType->getValue());
		self::assertSame('png', $pngMimeType->getExtension());

		$unknownMimeType = CustomMimeType::fromFilename('test.unknown');

		self::assertInstanceOf(CustomMimeType::class, $unknownMimeType);
		self::assertSame('application/octet-stream', $unknownMimeType->getValue());
		self::assertSame('unknown', $unknownMimeType->getExtension());
	}

	public function testFromEmptyFilename(): void
	{
		$empty = CustomMimeType::fromFilename('no-ext');
		$builtIn = MimeType::ApplicationOctetStream;

		self::assertInstanceOf(CustomMimeType::class, $empty);
		self::assertSame($builtIn->getValue(), $empty->getValue());
		self::assertSame('', $empty->getExtension());
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

		self::assertSame(MimeType::ImagePng, $mimeType);
	}
}
