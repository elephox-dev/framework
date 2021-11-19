<?php
declare(strict_types=1);

namespace Elephox\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Support\SplObjectIdHashGenerator
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

		self::assertEquals('val', $map->get('test'));
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
		self::assertEquals('123', $stringMap->get(0));
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
}
