<?php
declare(strict_types=1);

namespace Philly\Collection;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Philly\Collection\GenericWeakMap
 * @covers \Philly\Collection\OffsetNotFoundException
 * @covers \Philly\Collection\InvalidOffsetException
 */
class GenericWeakMapTest extends TestCase
{
	public function testConstructor(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap(
			[
				$inst,
			],
			[
				123,
			]
		);

		self::assertEquals(123, $map->get($inst));
	}

	public function testConstructorUnevenArrays(): void
	{
		$inst = new stdClass();
		$inst2 = new stdClass();

		$this->expectException(OffsetNotFoundException::class);

		new GenericWeakMap(
			[
				$inst,
				$inst2
			],
			[
				123,
			]
		);
	}

	public function testGetInvalidOffset(): void
	{
		$map = new GenericWeakMap();

		$this->expectException(OffsetNotFoundException::class);

		$map->get(new stdClass());
	}

	public function testFirst(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap([$inst], [123]);

		self::assertEquals(123, $map->first());
		self::assertEquals(123, $map->first(static fn(int $v) => $v > 100));
	}

	public function testWeakReference(): void
	{
		$map = new GenericWeakMap([new stdClass()], [123]);

		self::assertNull($map->first());
	}

	public function testWhere(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap([$inst], [123]);

		self::assertEquals(123, $map->where(static fn(int $v) => $v > 100)->first());
	}

	public function testHas(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap([$inst], [123]);

		self::assertTrue($map->has($inst));
	}

	public function testMap(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap([$inst], [123]);

		self::assertEquals(246, $map->map(static fn(int $v) => $v * 2)->first());
	}

	public function testAny(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap([$inst], [123]);

		self::assertTrue($map->any(static fn(int $v) => $v > 100));
	}
}