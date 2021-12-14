<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\ReadonlyMap;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Elephox\Collection\GenericWeakMap
 * @covers \Elephox\Collection\OffsetNotFoundException
 * @covers \Elephox\Collection\InvalidOffsetException
 * @covers \Elephox\Collection\ArrayList
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

	public function testFirstKey(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap([$inst], [123]);
		$emptyMap = new GenericWeakMap();

		self::assertSame($inst, $map->firstKey());
		self::assertSame($inst, $map->firstKey(static fn(object $k, int $v) => $v > 100));
		self::assertNull($emptyMap->firstKey());
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

	public function testAnyKey(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap([$inst], [123]);
		$emptyMap = new GenericWeakMap();

		self::assertTrue($map->anyKey(static fn(object $k) => $k instanceof stdClass));
		self::assertTrue($map->anyKey());
		self::assertFalse($emptyMap->anyKey());
	}

	public function testReduce(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap([$inst], [123]);

		self::assertEquals([123], $map->reduce(static fn(int $v) => $v)->asArray());
	}

	public function testValuesAndKeys(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap([$inst], [123]);

		self::assertEquals([$inst], $map->keys()->asArray());
		self::assertEquals([123], $map->values()->asArray());
	}

	public function testContains(): void
	{
		$inst = new stdClass();
		/** @var GenericWeakMap<stdClass, int> $map */
		$map = new GenericWeakMap([$inst], [123]);

		self::assertTrue($map->contains(123));
		self::assertFalse($map->contains(456));
	}

	public function testIterator(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap([$inst], [123]);

		foreach ($map as $key => $value) {
			self::assertEquals($inst, $key);
			self::assertEquals(123, $value);
		}
	}

	public function testWhereKey(): void
	{
		$a = new stdClass();
		$a->test = 'a';

		$b = new stdClass();
		$b->test = 'b';

		$map = new GenericWeakMap([$a, $b], [1, 2]);

		$filteredMap = $map->whereKey(static fn(object $k) => $k->test === 'a');

		self::assertEquals([$a], $filteredMap->keys()->asArray());
	}

	public function testMapKey(): void
	{
		$a = new stdClass();
		$a->test = 'a';

		$b = new stdClass();
		$b->test = 'b';

		$map = new GenericWeakMap([$a, $b], [1, 2]);

		$mapped = $map->mapKeys(static function (object $k) {
			$k->test .= '1';

			return $k;
		});

		self::assertEquals('a1', $mapped->keys()->first()->test);
	}

	public function testAsReadonly(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap([$inst], [123]);

		$readonlyMap = $map->asReadonly();

		self::assertInstanceOf(ReadonlyMap::class, $readonlyMap);
	}

	public function testRemove(): void
	{
		$inst = new stdClass();
		$map = new GenericWeakMap([$inst], [123]);

		$map->remove($inst);

		self::assertFalse($map->has($inst));
	}

	public function testDeepClone(): void
	{
		$anObject = new stdClass();
		$anObject->test = true;
		$anotherObject = new stdClass();
		$anotherObject->test = false;

		$map = new GenericWeakMap([$anObject, $anotherObject], [1, 2]);
		$clone = $map->deepClone();

		self::assertInstanceOf(GenericWeakMap::class, $clone);
		self::assertNotSame($map, $clone);
		self::assertNotSame($anObject, $clone->keys()[0]);
	}
}
