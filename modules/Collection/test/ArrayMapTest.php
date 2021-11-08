<?php
declare(strict_types=1);

namespace Philly\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Philly\Collection\ArrayMap
 * @covers \Philly\Support\SplObjectIdHashGenerator
 * @covers \Philly\Collection\OffsetNotFoundException
 * @covers \Philly\Collection\OffsetNotAllowedException
 * @covers \Philly\Collection\InvalidOffsetException
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

		$map->put(123.542, "test");
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
}
