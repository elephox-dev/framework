<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\GenericMap;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\OffsetNotFoundException
 * @covers \Elephox\Collection\OffsetNotAllowedException
 * @covers \Elephox\Collection\InvalidOffsetException
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\KeyValuePair
 * @covers \Elephox\Collection\DuplicateKeyException
 */
class ArrayMapTest extends TestCase
{
	public function testPutAndGet(): void
	{
		$map = new ArrayMap();

		$map->put('testKey', 'testValue');
		$map->put('anotherKey', 'anotherValue');

		self::assertEquals('testValue', $map->get('testKey'));
		self::assertEquals('anotherValue', $map->get('anotherKey'));
	}

	public function testInitialize(): void
	{
		$map = new ArrayMap(['test' => 'val', 123 => '134']);
		$map2 = ArrayMap::from(['test' => 'val', 123 => '134']);

		self::assertEquals('val', $map->get('test'));
		self::assertEquals('134', $map2->get(123));
	}

	public function testInvalidKey(): void
	{
		$map = new ArrayMap();

		$this->expectException(OffsetNotAllowedException::class);

		$map->put(false, "test");
	}

	public function testGetNotSet(): void
	{
		$map = new ArrayMap();

		$this->expectException(OffsetNotFoundException::class);

		$map->get('test');
	}

	public function testValuesAndKeys(): void
	{
		$map = new ArrayMap([
			'a' => '1',
			'b' => '2',
			'c' => '3',
		]);

		self::assertEquals(['1', '2', '3'], $map->values()->toList());
		self::assertEquals(['a', 'b', 'c'], $map->keys()->toList());
	}

	public function testContains(): void
	{
		$map = new ArrayMap([
			'a' => '1',
			'b' => '2',
			'c' => '3',
		]);

		self::assertTrue($map->contains('1'));
	}

	public function testIterator(): void
	{
		$map = new ArrayMap([
			'a' => '1',
		]);

		foreach ($map as $value) {
			self::assertEquals('1', $value);
		}
	}

	public function testRemove(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);

		$map->remove('a');

		self::assertCount(2, $map);
		self::assertFalse($map->has('a'));
	}

	public function testDeepClone(): void
	{
		$anObject = new stdClass();
		$anObject->test = true;
		$map = new ArrayMap(['obj' => $anObject, 'test' => 2, 'obj2' => $anObject]);
		$clone = $map->deepClone();

		self::assertInstanceOf(ArrayMap::class, $clone);
		self::assertNotSame($map, $clone);
		self::assertNotSame($map['obj'], $clone['obj']);
		self::assertNotSame($map['obj2'], $clone['obj2']);
		self::assertSame($clone['obj'], $clone['obj2']);
		self::assertTrue($clone['obj']->test);
	}

	public function testOffsetExists(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);

		self::assertTrue($map->offsetExists('a'));
		self::assertTrue($map->offsetExists('b'));
		self::assertTrue($map->offsetExists('c'));
		self::assertFalse($map->offsetExists('d'));
		self::assertFalse($map->offsetExists(null));
	}

	public function testOffsetSet(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);

		$map->offsetSet('a', 4);
		$map->offsetSet('d', 5);

		self::assertEquals(4, $map->get('a'));
		self::assertEquals(2, $map->get('b'));
		self::assertEquals(3, $map->get('c'));
		self::assertEquals(5, $map->get('d'));
	}

	public function testOffsetUnset(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);

		$map->offsetUnset('a');

		self::assertFalse($map->offsetExists('a'));
		self::assertTrue($map->offsetExists('b'));
	}
}
