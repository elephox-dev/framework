<?php
declare(strict_types=1);

namespace Elephox\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Http\HeaderName
 * @covers \Elephox\Http\CustomHeaderName
 */
class HeaderMapTest extends TestCase
{
	public function testParseHeaderName(): void
	{
		$contentType = HeaderMap::parseHeaderName('Content-Type');
		$custom = HeaderMap::parseHeaderName('X-Custom');

		self::assertInstanceOf(HeaderName::class, $contentType);
		self::assertInstanceOf(CustomHeaderName::class, $custom);
		self::assertEquals('X-Custom', $custom->getValue());
	}
}
