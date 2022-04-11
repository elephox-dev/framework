<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\OffsetNotAllowedException
 * @covers \Elephox\Collection\OffsetNotFoundException
 * @covers \Elephox\Collection\InvalidOffsetException
 * @covers \Elephox\Collection\DefaultEqualityComparer
 *
 * @internal
 */
class ArrayListTest extends TestCase
{
	public function testOffsetExists(): void
	{
		$arr = new ArrayList(['test']);

		static::assertTrue($arr->offsetExists(0));
		static::assertArrayHasKey(0, $arr);
		static::assertFalse($arr->offsetExists(1));
	}

	public function testCount(): void
	{
		$arr = new ArrayList(['test', 'test2', 'test3']);

		static::assertCount(3, $arr);
	}

	public function testOffsetUnset(): void
	{
		$arr = new ArrayList(['test', 'test2', 'test3']);

		static::assertCount(3, $arr);

		$arr->offsetUnset(1);

		static::assertCount(2, $arr);
	}

	public function testOffsetSet(): void
	{
		$arr = new ArrayList();

		static::assertArrayNotHasKey(0, $arr);
		static::assertArrayNotHasKey(1, $arr);
		static::assertArrayNotHasKey(2, $arr);

		$arr->offsetSet(null, 'test');
		$arr->put(0, 'test2');
		$arr[null] = 'test3';

		static::assertArrayHasKey(0, $arr);
		static::assertArrayHasKey(1, $arr);
		static::assertArrayNotHasKey(2, $arr);
		static::assertEquals('test2', $arr[0]);
		static::assertEquals('test3', $arr[1]);
	}

	public function testOffsetSetInvalidType(): void
	{
		$arr = new ArrayList();

		$this->expectException(OffsetNotAllowedException::class);

		$arr->offsetSet('not a number', 'test');
	}

	public function testOffsetSetOutsideOfArray(): void
	{
		$arr = new ArrayList();

		$this->expectException(InvalidArgumentException::class);

		$arr->offsetSet(10, 'test');
	}

	public function testOffsetGet(): void
	{
		$arr = new ArrayList(['test', 'test2', 'test3']);

		static::assertEquals('test', $arr->offsetGet(0));
		static::assertEquals('test2', $arr[1]);

		$this->expectException(OffsetNotAllowedException::class);
		$this->expectExceptionMessage("Offset 'not a number' is not allowed.");

		$arr->offsetGet('not a number');
	}

	public function testElementAt(): void
	{
		$arr = new ArrayList(['test', 'test2']);

		$this->expectException(OffsetNotFoundException::class);
		$this->expectExceptionMessage("Offset '123' does not exist.");

		$arr->elementAt(123);
	}

	public function testAdd(): void
	{
		$arr = new ArrayList();

		static::assertCount(0, $arr);

		$arr->add('test');
		$arr[] = 'test2';

		static::assertCount(2, $arr);
		static::assertEquals('test', $arr->elementAt(0));
		static::assertEquals('test2', $arr->elementAt(1));
	}

	public function testAddAll(): void
	{
		$arr = new ArrayList();

		static::assertCount(0, $arr);

		$arr->addAll(['test', 'test2']);

		static::assertCount(2, $arr);
		static::assertEquals('test', $arr->elementAt(0));
		static::assertEquals('test2', $arr->elementAt(1));
	}

	public function testIsEmpty(): void
	{
		$filled = new ArrayList(['653', '123', '154']);
		$empty = new ArrayList();

		static::assertFalse($filled->isEmpty());
		static::assertTrue($empty->isEmpty());
	}

	public function testToList(): void
	{
		$arr = new ArrayList(['123', '456']);

		static::assertEquals(['123', '456'], $arr->toList());
	}

	public function testFromArray(): void
	{
		$arr = ArrayList::from(['123', '456']);

		static::assertEquals('123', $arr->elementAt(0));
		static::assertEquals('456', $arr->elementAt(1));
	}

	public function testFromSelf(): void
	{
		$arr = ArrayList::from(['123', '456']);
		$arr2 = ArrayList::from($arr);

		static::assertSame($arr, $arr2);
	}

	public function testFromIterator(): void
	{
		$arr = ArrayList::from(new ArrayIterator(['123', '456']));

		static::assertEquals('123', $arr->elementAt(0));
		static::assertEquals('456', $arr->elementAt(1));
	}

	public function testFromValue(): void
	{
		$arr = ArrayList::from('123');

		static::assertEquals('123', $arr->elementAt(0));
	}

	public function testIterator(): void
	{
		$map = new ArrayList([
			'a' => '1',
		]);

		foreach ($map as $value) {
			static::assertEquals('1', $value);
		}
	}

	public function testRemoveAt(): void
	{
		$list = new ArrayList([1, 2, 3, 4, 5]);

		static::assertCount(5, $list);

		$removed = $list->removeAt(2);

		static::assertEquals(3, $removed);
		static::assertEquals(4, $list->count());
		static::assertEquals(1, $list->elementAt(0));
		static::assertEquals(2, $list->elementAt(1));
		static::assertEquals(4, $list->elementAt(2));
		static::assertEquals(5, $list->elementAt(3));
	}

	public function testRemove(): void
	{
		$list = new ArrayList([1, 2, 3, 4, 5]);

		static::assertCount(5, $list);

		$removed = $list->remove(3);

		static::assertTrue($removed);
		static::assertEquals(4, $list->count());
		static::assertEquals(1, $list->elementAt(0));
		static::assertEquals(2, $list->elementAt(1));
		static::assertEquals(4, $list->elementAt(2));
		static::assertEquals(5, $list->elementAt(3));
	}

	public function testOffsetExistThrows(): void
	{
		$this->expectException(OffsetNotAllowedException::class);
		$this->expectExceptionMessage("Offset 'not a number' is not allowed.");

		$arr = new ArrayList();
		$arr->offsetExists('not a number');
	}

	public function testOffsetUnsetThrows(): void
	{
		$this->expectException(OffsetNotAllowedException::class);
		$this->expectExceptionMessage("Offset 'not a number' is not allowed.");

		$arr = new ArrayList();
		$arr->offsetUnset('not a number');
	}

	public function testIndexOf(): void
	{
		$list = new ArrayList([1, 2, 3, 4, 5]);

		static::assertEquals(0, $list->indexOf(1));
		static::assertEquals(1, $list->indexOf(2));
		static::assertEquals(2, $list->indexOf(3));
		static::assertEquals(3, $list->indexOf(4));
		static::assertEquals(4, $list->indexOf(5));
		static::assertEquals(-1, $list->indexOf(6));
	}

	public function testLastIndexOf(): void
	{
		$list = new ArrayList([1, 2, 3, 4, 5]);

		static::assertEquals(4, $list->lastIndexOf(5));
		static::assertEquals(3, $list->lastIndexOf(4));
		static::assertEquals(2, $list->lastIndexOf(3));
		static::assertEquals(1, $list->lastIndexOf(2));
		static::assertEquals(0, $list->lastIndexOf(1));
		static::assertEquals(-1, $list->lastIndexOf(0));
		static::assertEquals(-1, $list->lastIndexOf(7));
	}

	public function testRemoveNotExists(): void
	{
		$list = new ArrayList([1, 2, 3]);

		static::assertFalse($list->remove(4));
	}

	public function testRemoveAtOffsetNotExists(): void
	{
		$list = new ArrayList([1, 2, 3]);

		$this->expectException(OffsetNotFoundException::class);
		$list->removeAt(4);
	}
}
