<?php
declare(strict_types=1);

namespace Elephox\Http;

use AssertionError;
use Elephox\Http\Contract\Cookie as CookieContract;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Elephox\Http\CookieMap
 * @covers \Elephox\Http\Cookie
 * @covers \Elephox\Collection\ArrayMap
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
class CookieMapTest extends TestCase
{
	public function testFromGlobals(): void
	{
		$map = CookieMap::fromGlobals(['foo' => 'bar', 'baz' => null]);

		static::assertInstanceOf(CookieMap::class, $map);
		static::assertInstanceOf(CookieContract::class, $map->get('foo'));
		static::assertSame('bar', $map->get('foo')->getValue());
	}

	public function testFromGlobalsNull(): void
	{
		$mapNoArg = CookieMap::fromGlobals();
		$mapEmpty = CookieMap::fromGlobals([]);

		static::assertEmpty($mapNoArg);
		static::assertEmpty($mapEmpty);
	}

	public function testFromGlobalsInvalidValueType(): void
	{
		$this->expectException(AssertionError::class);
		$this->expectExceptionMessage('CookieMap::fromGlobals() expects an array of strings with string keys');

		CookieMap::fromGlobals(['test' => new stdClass()]);
	}

	public function testFromGlobalsInvalidKeyType(): void
	{
		$this->expectException(AssertionError::class);
		$this->expectExceptionMessage('CookieMap::fromGlobals() expects an array of strings with string keys');

		CookieMap::fromGlobals(['test']);
	}
}
