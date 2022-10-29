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
class ArrayMapTest extends TestCase
{
	public function testFromSelf(): void
	{
		$arr = new ArrayMap();
		$arr2 = ArrayMap::from($arr);

		static::assertSame($arr, $arr2);
	}

	public function testFromIterator(): void
	{
		$iterator = new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]);
		$arr = ArrayMap::from($iterator);

		static::assertSame(['a' => 1, 'b' => 2, 'c' => 3], $arr->toArray());
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

		static::assertSame('testValue', $map->get('testKey'));
		static::assertSame('anotherValue', $map->get('anotherKey'));
	}

	public function testInitialize(): void
	{
		$map = new ArrayMap(['test' => 'val', 123 => '134']);
		$map2 = ArrayMap::from(['test' => 'val', 123 => '134']);

		static::assertSame('val', $map->get('test'));
		static::assertSame('134', $map2->get(123));
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

		static::assertSame(['1', '2', '3'], $map->values()->toList());
		static::assertSame(['a', 'b', 'c'], $map->keys()->toList());
	}

	public function testContains(): void
	{
		$map = new ArrayMap([
			'a' => '1',
			'b' => '2',
			'c' => '3',
		]);

		static::assertTrue($map->contains('1'));
	}

	public function testIterator(): void
	{
		$map = new ArrayMap([
			'a' => '1',
		]);

		foreach ($map as $value) {
			static::assertSame('1', $value);
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

		static::assertTrue($removed);
		static::assertCount(2, $map);
		static::assertFalse($map->has('a'));
	}

	public function testRemoveNonExistent(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);

		$removed = $map->remove('d');

		static::assertFalse($removed);
		static::assertCount(3, $map);
	}

	public function testRemoveIntKey(): void
	{
		$map = new ArrayMap(['a', 'b', 'c']);

		$removed = $map->remove(1);

		static::assertTrue($removed);
		static::assertCount(2, $map);
		static::assertSame(['a', 'c'], $map->toArray());
	}

	public function testOffsetExists(): void
	{
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);

		static::assertTrue($map->offsetExists('a'));
		static::assertTrue($map->offsetExists('b'));
		static::assertTrue($map->offsetExists('c'));
		static::assertFalse($map->offsetExists('d'));
		static::assertFalse($map->offsetExists(null));
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

		static::assertSame(4, $map->offsetGet('a'));
		static::assertSame(2, $map->offsetGet('b'));
		static::assertSame(3, $map->offsetGet('c'));
		static::assertSame(5, $map->offsetGet('d'));

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

		static::assertFalse($map->offsetExists('a'));
		static::assertTrue($map->offsetExists('b'));
	}

	public function testFirstOrDefault(): void
	{
		$emptyMap = new ArrayMap();
		$map = new ArrayMap([
			'a' => 1,
			'b' => 2,
			'c' => 3,
		]);

		static::assertNull($emptyMap->firstOrDefault(null));
		static::assertSame(1, $map->firstOrDefault(null));
		static::assertSame(2, $map->firstOrDefault(null, static fn (int $v) => $v % 2 === 0));
	}
}
