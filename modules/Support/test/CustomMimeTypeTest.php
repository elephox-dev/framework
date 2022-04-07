<?php
declare(strict_types=1);

namespace Elephox\Support;

use Elephox\Mimey\MimeType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Support\CustomMimeType
 *
 * @internal
 */
class CustomMimeTypeTest extends TestCase
{
	public function testInstantiate(): void
	{
		$mimeType = new CustomMimeType('image/png');
		$builtIn = MimeType::ImagePng;

		static::assertInstanceOf(CustomMimeType::class, $mimeType);
		static::assertEquals($builtIn->getValue(), $mimeType->getValue());
	}
}
