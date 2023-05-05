<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\ArrayMap
 * @covers \Elephox\Collection\OffsetNotFoundException
 * @covers \Elephox\Collection\OffsetNotAllowedException
 * @covers \Elephox\Collection\InvalidOffsetException
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\Enumerable
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @uses \Elephox\Collection\IsEnumerable
 *
 * @internal
 */
final class ArrayMapTest extends TestCase
{
	public function testFromSelf(): void
	{
		$arr = new ArrayMap();
		$arr2 = ArrayMap::from($arr);

		self::assertSame($arr, $arr2);
	}

	public function testFromIterator(): void
	{
		$iterator = new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]);
		$arr = ArrayMap::from($iterator);

		self::assertSame(['a' => 1, 'b' => 2, 'c' => 3], $arr->toArray());
	}

	public function testFromInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);

		ArrayMap::from('test');
	}

	public function testPutAndGet(): void
	{
		$map = new ArrayMap();

		$map->put('testKey', 'testValue');
		$map->put('anotherKey', 'anotherValue');

		self::assertSame('testValue', $map->get('testKey'));
		self::assertSame('anotherValue', $map->get('anotherKey'));
	}

	public function testInitialize(): void
	{
		$map = new ArrayMap(['test' => 'val', 123 => '134']);
		$map2 = ArrayMap::from(['test' => 'val', 123 => '134']);

		self::assertSame('val', $map->get('test'));
		self::assertSame('134', $map2->get(123));
	}

	public function testInvalidKey(): void
	{
		$map = new ArrayMap();

		$this->expectException(OffsetNotAllowedException::class);

		$map->put(false, 'test');
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

		self::assertSame(['1', '2', '3'], $map->values()->toList());
		self::assertSame(['a', 'b', 'c'], $map->keys()->toList());
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
			self::assertSame('1', $value);
		}
	}

	public function testRemove(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);

		$removed = $map->remove('a');

		self::assertTrue($removed);
		self::assertCount(2, $map);
		self::assertFalse($map->has('a'));
	}

	public function testRemoveNonExistent(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);

		$removed = $map->remove('d');

		self::assertFalse($removed);
		self::assertSame([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		], $map->toArray());
	}

	public function testRemoveIntKey(): void
	{
		$map = new ArrayMap(['a', 'b', 'c']);

		$removed = $map->remove(1);

		self::assertTrue($removed);
		self::assertCount(2, $map);
		self::assertSame(['a', 'c'], $map->toArray());
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

		self::assertSame(4, $map->offsetGet('a'));
		self::assertSame(2, $map->offsetGet('b'));
		self::assertSame(3, $map->offsetGet('c'));
		self::assertSame(5, $map->offsetGet('d'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Cannot set null offset in ArrayMap');

		$map->offsetSet(null, 'test');
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

	public function testFirstOrDefault(): void
	{
		$emptyMap = new ArrayMap();
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);

		self::assertNull($emptyMap->firstOrDefault(null));
		self::assertSame(1, $map->firstOrDefault(null));
		self::assertSame(2, $map->firstOrDefault(null, static fn (int $v) => $v % 2 === 0));
	}

	public function testCount(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
			'd' => 4,
		]);

		self::assertCount(4, $map);
		self::assertSame(4, $map->count());
		self::assertSame(2, $map->count(static fn (int $v) => $v % 2 === 0));
	}
}
