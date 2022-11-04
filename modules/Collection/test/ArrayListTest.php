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
 * @covers \Elephox\Collection\EmptySequenceException
 * @covers \Elephox\Collection\Iterator\ReverseIterator
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\Collection\IteratorProvider
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
		static::assertSame('test2', $arr[0]);
		static::assertSame('test3', $arr[1]);
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

		static::assertSame('test', $arr->offsetGet(0));
		static::assertSame('test2', $arr[1]);

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
		static::assertSame('test', $arr->elementAt(0));
		static::assertSame('test2', $arr->elementAt(1));
	}

	public function testAddAll(): void
	{
		$arr = new ArrayList();

		static::assertCount(0, $arr);

		$arr->addAll(['test', 'test2']);

		static::assertCount(2, $arr);
		static::assertSame('test', $arr->elementAt(0));
		static::assertSame('test2', $arr->elementAt(1));
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

		static::assertSame(['123', '456'], $arr->toList());
	}

	public function testFromArray(): void
	{
		$arr = ArrayList::from(['123', '456']);

		static::assertSame('123', $arr->elementAt(0));
		static::assertSame('456', $arr->elementAt(1));
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

		static::assertSame('123', $arr->elementAt(0));
		static::assertSame('456', $arr->elementAt(1));
	}

	public function testFromValue(): void
	{
		$arr = ArrayList::from('123');

		static::assertSame('123', $arr->elementAt(0));
	}

	public function testIterator(): void
	{
		$map = new ArrayList([
			'a' => '1',
		]);

		foreach ($map as $value) {
			static::assertSame('1', $value);
		}
	}

	public function testRemoveAt(): void
	{
		$list = new ArrayList([1, 2, 3, 4, 5]);

		static::assertCount(5, $list);

		$removed = $list->removeAt(2);

		static::assertSame(3, $removed);
		static::assertSame(4, $list->count());
		static::assertSame(1, $list->elementAt(0));
		static::assertSame(2, $list->elementAt(1));
		static::assertSame(4, $list->elementAt(2));
		static::assertSame(5, $list->elementAt(3));
	}

	public function testRemove(): void
	{
		$list = new ArrayList([1, 2, 3, 4, 5]);

		static::assertCount(5, $list);

		$removed = $list->removeValue(3);

		static::assertTrue($removed);
		static::assertSame(4, $list->count());
		static::assertSame(1, $list->elementAt(0));
		static::assertSame(2, $list->elementAt(1));
		static::assertSame(4, $list->elementAt(2));
		static::assertSame(5, $list->elementAt(3));
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

		static::assertSame(0, $list->indexOf(1));
		static::assertSame(1, $list->indexOf(2));
		static::assertSame(2, $list->indexOf(3));
		static::assertSame(3, $list->indexOf(4));
		static::assertSame(4, $list->indexOf(5));
		static::assertSame(-1, $list->indexOf(6));
	}

	public function testLastIndexOf(): void
	{
		$list = new ArrayList([1, 2, 3, 4, 5]);

		static::assertSame(4, $list->lastIndexOf(5));
		static::assertSame(3, $list->lastIndexOf(4));
		static::assertSame(2, $list->lastIndexOf(3));
		static::assertSame(1, $list->lastIndexOf(2));
		static::assertSame(0, $list->lastIndexOf(1));
		static::assertSame(-1, $list->lastIndexOf(0));
		static::assertSame(-1, $list->lastIndexOf(7));
	}

	public function testRemoveNotExists(): void
	{
		$list = new ArrayList([1, 2, 3]);

		static::assertFalse($list->removeValue(4));
	}

	public function testRemoveAtOffsetNotExists(): void
	{
		$list = new ArrayList([1, 2, 3]);

		$this->expectException(OffsetNotFoundException::class);
		$list->removeAt(4);
	}

	public function testFromThrowsForNonList(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('ArrayList::from() expects a list of values');

		ArrayList::from(['a' => 1, 'b' => 2]);
	}

	public function testPop(): void
	{
		$list = new ArrayList([1, 2, 3]);

		static::assertSame(3, $list->pop());
		static::assertSame(2, $list->pop());
		static::assertSame(1, $list->pop());
		static::assertSame(0, $list->count());
	}

	public function testPopWithPredicate(): void
	{
		$list = new ArrayList([1, 2, 3]);

		static::assertSame(3, $list->pop(static fn ($item) => $item % 2 === 1));
		static::assertSame(1, $list->pop(static fn ($item) => $item % 2 === 1));
		static::assertSame(1, $list->count());
	}

	public function testPopThrowsForEmpty(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		$list = new ArrayList();
		$list->pop();
	}

	public function testPopWithPredicateThrowsForEmpty(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		$list = new ArrayList([1, 3]);
		$list->pop(static fn ($item) => $item % 2 === 0);
	}

	public function testShift(): void
	{
		$list = new ArrayList([1, 2, 3]);

		static::assertSame(1, $list->shift());
		static::assertSame(2, $list->shift());
		static::assertSame(3, $list->shift());
		static::assertSame(0, $list->count());
	}

	public function testShiftWithPredicate(): void
	{
		$list = new ArrayList([1, 2, 3]);

		static::assertSame(1, $list->shift(static fn ($item) => $item % 2 === 1));
		static::assertSame(3, $list->shift(static fn ($item) => $item % 2 === 1));
		static::assertSame(1, $list->count());
	}

	public function testShiftThrowsForEmpty(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		$list = new ArrayList();
		$list->shift();
	}

	public function testShiftWithPredicateThrowsForEmpty(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		$list = new ArrayList([1, 3]);
		$list->shift(static fn ($item) => $item % 2 === 0);
	}

	public function testUnshift(): void
	{
		$list = new ArrayList([1, 2, 3]);

		$list->unshift(0);

		static::assertSame(0, $list[0]);
		static::assertSame(1, $list[1]);
	}

	public function testImplode(): void
	{
		$list = new ArrayList([1, 2, 3]);

		static::assertSame('1, 2, 3', $list->implode());
	}

	public function testImplodeWithGlue(): void
	{
		$list = new ArrayList([1, 2, 3]);

		static::assertSame('1-2-3', $list->implode('-'));
	}

	public function testContains(): void
	{
		$list = new ArrayList([1, 2, 3]);

		static::assertTrue($list->contains(1));
		static::assertTrue($list->contains(2));
		static::assertTrue($list->contains(3));
		static::assertFalse($list->contains(4));
	}

	public function testInsertAt(): void
	{
		$list = new ArrayList(['a', 'b', 'c']);

		$list->insertAt(0, 'z');
		$list->insertAt(2, '-');
		$list->insertAt(5, 'd');

		static::assertCount(6, $list);
		static::assertSame(['z', 'a', '-', 'b', 'c', 'd'], $list->toArray());
	}

	public function testSlice(): void
	{
		$list = new ArrayList(range(0, 5));

		$start = $list->slice(0, 2);
		static::assertCount(2, $start);
		static::assertSame(0, $start[0]);
		static::assertSame(1, $start[1]);

		$end = $list->slice(4, 2);
		static::assertCount(2, $end);
		static::assertSame(4, $end[0]);
		static::assertSame(5, $end[1]);
	}
}
