<?php
declare(strict_types=1);

namespace Elephox\Collection;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Elephox\Collection\ObjectMap
 * @covers \Elephox\Collection\OffsetNotFoundException
 * @covers \Elephox\Collection\InvalidOffsetException
 * @covers \Elephox\Collection\ArrayList
 */
class ObjectMapTest extends TestCase
{
	public function testConstructor(): void
	{
		$inst = new stdClass();
		$map = new ObjectMap(
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

		new ObjectMap(
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
		$map = new ObjectMap();

		$this->expectException(OffsetNotFoundException::class);

		$map->get(new stdClass());
	}

	public function testFirst(): void
	{
		$inst = new stdClass();
		$map = new ObjectMap([$inst], [123]);

		self::assertEquals(123, $map->first());
		self::assertEquals(123, $map->first(static fn(int $v) => $v > 100));
	}

	public function testFirstKey(): void
	{
		$inst = new stdClass();
		$map = new ObjectMap([$inst], [123]);
		$emptyMap = new ObjectMap();

		self::assertSame($inst, $map->firstKey());
		self::assertSame($inst, $map->firstKey(static fn(object $k, int $v) => $v > 100));
		self::assertNull($emptyMap->firstKey());
	}

	public function testReference(): void
	{
		$map = new ObjectMap([new stdClass()], [123]);

		self::assertInstanceOf(stdClass::class, $map->firstKey());
	}

	public function testWhere(): void
	{
		$inst = new stdClass();
		$map = new ObjectMap([$inst], [123]);

		self::assertEquals(123, $map->where(static fn(int $v) => $v > 100)->first());
	}

	public function testHas(): void
	{
		$inst = new stdClass();
		$map = new ObjectMap([$inst], [123]);

		self::assertTrue($map->has($inst));
	}

	public function testMap(): void
	{
		$inst = new stdClass();
		$map = new ObjectMap([$inst], [123]);

		self::assertEquals(246, $map->map(static fn(int $v) => $v * 2)->first());
	}

	public function testAny(): void
	{
		$inst = new stdClass();
		$map = new ObjectMap([$inst], [123]);

		self::assertTrue($map->any(static fn(int $v) => $v > 100));
	}

	public function testAnyKey(): void
	{
		$inst = new stdClass();
		$map = new ObjectMap([$inst], [123]);
		$emptyMap = new ObjectMap();

		self::assertTrue($map->anyKey(static fn(object $k) => $k instanceof stdClass));
		self::assertTrue($map->anyKey());
		self::assertFalse($emptyMap->anyKey());
	}

	public function testReduce(): void
	{
		$inst = new stdClass();
		$map = new ObjectMap([$inst], [123]);

		self::assertEquals([123], $map->reduce(static fn(int $v) => $v)->asArray());
	}

	public function testValuesAndKeys(): void
	{
		$inst = new stdClass();
		$map = new ObjectMap([$inst], [123]);

		self::assertEquals([$inst], $map->keys()->asArray());
		self::assertEquals([123], $map->values()->asArray());
	}

	public function testContains(): void
	{
		$inst = new stdClass();
		/** @var ObjectMap<stdClass, int> $map */
		$map = new ObjectMap([$inst], [123]);

		self::assertTrue($map->contains(123));
		self::assertFalse($map->contains(456));
	}

	public function testIterator(): void
	{
		$inst = new stdClass();
		$map = new ObjectMap([$inst], [123]);

		foreach ($map as $key => $value) {
			self::assertEquals($inst, $key);
			self::assertEquals(123, $value);
		}
	}
}
