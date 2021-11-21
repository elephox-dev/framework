<?php
declare(strict_types=1);

namespace Elephox\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\CustomHeaderName
 */
class CustomHeaderNameTest extends TestCase
{
	public function testConstructor(): void
	{
		$header = new CustomHeaderName('X-Custom-Header');
		self::assertEquals('X-Custom-Header', $header->getValue());
		self::assertTrue($header->canBeDuplicate());
		self::assertFalse($header->isOnlyResponse());
		self::assertFalse($header->isOnlyRequest());
	}
}
