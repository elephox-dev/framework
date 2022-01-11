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
		/**
		 * @var ArrayMap<string, mixed> $map
		 * @noinspection PhpRedundantVariableDocTypeInspection
		 */
		$map = new ArrayMap();

		$map->put('testKey', 'testValue');
		$map->put('anotherKey', 'anotherValue');

		self::assertEquals('testValue', $map->get('testKey'));
		self::assertEquals('anotherValue', $map->get('anotherKey'));
	}

	public function testInitialize(): void
	{
		$map = new ArrayMap(['test' => 'val', 123 => '134']);
		$map2 = ArrayMap::fromIterable(['test' => 'val', 123 => '134']);

		self::assertEquals('val', $map->get('test'));
		self::assertEquals('134', $map2->get(123));
	}

	public function testInvalidKey(): void
	{
		$map = new ArrayMap();

		$this->expectException(OffsetNotAllowedException::class);

		$map->put(false, "test");
	}

	public function testFirst(): void
	{
		$map = new ArrayMap(['653', '123', '1543']);

		self::assertEquals('653', $map->first());
		self::assertEquals('123', $map->first(fn(string $a) => $a[0] === '1'));
	}

	public function testWhere(): void
	{
		$map = new ArrayMap(['653', '123', '154']);
		$res = $map->where(fn(string $a) => str_ends_with($a, '3'));

		self::assertEquals('653', $res->get(0));
	}

	public function testFirstNull(): void
	{
		$map = new ArrayMap();

		self::assertNull($map->first());
	}

	public function testGetNotSet(): void
	{
		$map = new ArrayMap();

		$this->expectException(OffsetNotFoundException::class);

		$map->get('test');
	}

	public function testMap(): void
	{
		$map = new ArrayMap([123]);

		$stringMap = $map->map(fn(int $a) => (string)$a);

		self::assertNotSame($map, $stringMap);
		self::assertSame('123', $stringMap->get(0));
	}

	public function testAny(): void
	{
		$map = new ArrayMap([123, 345, 567]);

		self::assertTrue($map->any());
		self::assertTrue($map->any(fn(int $a) => $a > 500));
		self::assertFalse($map->any(fn(int $a) => $a < 100));
	}

	public function testFromKeyValuePairList(): void
	{
		$list = new ArrayList([
			new KeyValuePair('test', 'val'),
		]);

		$map = ArrayMap::fromKeyValuePairList($list);
		self::assertEquals('val', $map->get('test'));

		$list->add(new KeyValuePair('test', 'val2'));

		$this->expectException(DuplicateKeyException::class);
		ArrayMap::fromKeyValuePairList($list);
	}

	public function testValuesAndKeys(): void
	{
		$map = new ArrayMap([
			'a' => '1',
			'b' => '2',
			'c' => '3',
		]);

		self::assertEquals(['1', '2', '3'], $map->values()->asArray());
		self::assertEquals(['a', 'b', 'c'], $map->keys()->asArray());
	}

	public function testReduce(): void
	{
		$map = new ArrayMap([
			'a' => '1',
			'b' => '2',
			'c' => '3',
		]);

		self::assertEquals(['a1', 'b2', 'c3'], $map->reduce(fn(string $val, string $key) => $key . $val)->asArray());
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

	public function testFirstKey(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);
		$emptyMap = new ArrayMap();

		self::assertEquals('a', $map->firstKey());
		self::assertEquals('b', $map->firstKey(fn($key, $val) => $val > 1));
		self::assertNull($emptyMap->firstKey());
	}

	public function testAnyKey(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);
		$emptyMap = new ArrayMap();

		self::assertTrue($map->anyKey());
		self::assertTrue($map->anyKey(fn($key) => $key > 'a'));
		self::assertFalse($emptyMap->anyKey());
	}

	public function testMapKeys(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);

		self::assertEquals(['a1' => 1, 'b1' => 2, 'c1' => 3], $map->mapKeys(fn($key) => $key . '1')->asArray());
	}

	public function testWhereKey(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);

		self::assertEquals(['b' => 2, 'c' => 3], $map->whereKey(fn($key) => $key > 'a')->asArray());
	}

	public function testToJson(): void
	{
		$map = new ArrayMap(['test' => 123, 'arr' => ['abc', 'def', 'ghi']]);
		$json = $map->toJson();

		self::assertEquals('{"test":123,"arr":["abc","def","ghi"]}', $json);
	}

	public function testAsReadonly(): void
	{
		$map = new ArrayMap([123, 456]);
		$readonly = $map->asReadonly();

		self::assertInstanceOf(GenericMap::class, $readonly);
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
