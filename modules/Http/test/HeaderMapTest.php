<?php
declare(strict_types=1);

namespace Elephox\Http;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\HeaderMap
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\OOR\Casing
 * @covers \Elephox\Http\HeaderName
 * @covers \Elephox\Collection\Iterator\FlipIterator
 *
 * @internal
 */
final class HeaderMapTest extends TestCase
{
	public function testFromGlobals(): void
	{
		$map = HeaderMap::fromGlobals([
			'HTTP_ACCEPT' => 'text/html',
			'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
			'TEST_HEADER' => 'test',
		]);

		self::assertTrue($map->has(HeaderName::Accept));
		self::assertSame(['text/html'], $map->get(HeaderName::Accept));

		self::assertTrue($map->has(HeaderName::AcceptLanguage));
		self::assertSame(['en-US,en;q=0.9'], $map->get(HeaderName::AcceptLanguage));

		self::assertFalse($map->has('TEST_HEADER'));
		self::assertFalse($map->has('TestHeader'));
		self::assertFalse($map->has('Test-Header'));
		self::assertFalse($map->has('Header'));
	}
}
