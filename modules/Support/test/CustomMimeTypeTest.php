<?php
declare(strict_types=1);

namespace Elephox\Support;

use Mimey\MimeType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Support\CustomMimeType
 */
class CustomMimeTypeTest extends TestCase
{
	public function testInstantiate(): void
	{
		$mimeType = new CustomMimeType('image/png');
		$builtIn = MimeType::ImagePng;

		self::assertInstanceOf(CustomMimeType::class, $mimeType);
		self::assertEquals($builtIn->getValue(), $mimeType->getValue());
	}
}
