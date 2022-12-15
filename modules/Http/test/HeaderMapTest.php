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
class HeaderMapTest extends TestCase
{
	public function testFromGlobals(): void
	{
		$map = HeaderMap::fromGlobals([
			'HTTP_ACCEPT' => 'text/html',
			'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
			'TEST_HEADER' => 'test',
		]);

		static::assertTrue($map->has(HeaderName::Accept));
		static::assertSame(['text/html'], $map->get(HeaderName::Accept));

		static::assertTrue($map->has(HeaderName::AcceptLanguage));
		static::assertSame(['en-US,en;q=0.9'], $map->get(HeaderName::AcceptLanguage));

		static::assertFalse($map->has('TEST_HEADER'));
		static::assertFalse($map->has('TestHeader'));
		static::assertFalse($map->has('Test-Header'));
		static::assertFalse($map->has('Header'));
	}
}
