<?php
declare(strict_types=1);

namespace Elephox\Support;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Support\CustomMimeType
 * @covers \Elephox\Support\MimeType
 */
class CustomMimeTypeTest extends TestCase
{
	public function testInstantiate(): void
	{
		$mimeType = new CustomMimeType('image/png');
		$builtIn = MimeType::Imagepng;

		self::assertInstanceOf(CustomMimeType::class, $mimeType);
		self::assertEquals($builtIn->getValue(), $mimeType->getValue());
	}
}
