<?php
declare(strict_types=1);

namespace Elephox\Http;

use Elephox\Http\Contract\Cookie as CookieContract;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Http\CookieMap
 * @covers \Elephox\Http\Cookie
 * @covers \Elephox\Collection\ArrayMap
 * @uses \Elephox\Collection\IsKeyedEnumerable
 */
class CookieMapTest extends TestCase
{
	public function testFromGlobals(): void
	{
		$map = CookieMap::fromGlobals(['foo' => 'bar']);

		self::assertInstanceOf(CookieMap::class, $map);
		self::assertInstanceOf(CookieContract::class, $map->get('foo'));
		self::assertEquals('bar', $map->get('foo')->getValue());
	}

	public function testFromGlobalsNull(): void
	{
		$mapNoArg = CookieMap::fromGlobals();
		$mapEmpty = CookieMap::fromGlobals([]);

		self::assertEmpty($mapNoArg);
		self::assertEmpty($mapEmpty);
	}

	public function testFromGlobalsInvalidValueType(): void
	{
		$this->expectException(InvalidArgumentException::class);

		CookieMap::fromGlobals(['test' => new \stdClass()]);
	}

	public function testFromGlobalsInvalidKeyType(): void
	{
		$this->expectException(InvalidArgumentException::class);

		CookieMap::fromGlobals(['test']);
	}
}
