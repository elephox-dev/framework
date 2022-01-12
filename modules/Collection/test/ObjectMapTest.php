<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\GenericMap;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Elephox\Collection\ObjectMap
 * @covers \Elephox\Collection\OffsetNotFoundException
 * @covers \Elephox\Collection\InvalidOffsetException
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Collection\Enumerable
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

	public function testHas(): void
	{
		$inst = new stdClass();
		$map = new ObjectMap([$inst], [123]);

		self::assertTrue($map->has($inst));
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

	public function testDeepClone(): void
	{
		$anObject = new stdClass();
		$anObject->test = true;
		$anotherObject = new stdClass();
		$anotherObject->test = false;

		$map = new ObjectMap([$anObject], [$anotherObject]);
		$clone = $map->deepClone();

		self::assertInstanceOf(ObjectMap::class, $clone);
		self::assertNotSame($map, $clone);
		self::assertNotSame($anObject, $clone->keys()->first());
		self::assertNotSame($anotherObject, $clone->values()->first());
	}
}
