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
final class ArrayListTest extends TestCase
{
	public function testOffsetExists(): void
	{
		$arr = new ArrayList(['test']);

		self::assertTrue($arr->offsetExists(0));
		self::assertArrayHasKey(0, $arr);
		self::assertFalse($arr->offsetExists(1));
	}

	public function testCount(): void
	{
		$arr = new ArrayList(['test', 'test2', 'test3']);

		self::assertCount(3, $arr);
	}

	public function testOffsetUnset(): void
	{
		$arr = new ArrayList(['test', 'test2', 'test3']);

		self::assertCount(3, $arr);

		$arr->offsetUnset(1);

		self::assertCount(2, $arr);
	}

	public function testOffsetSet(): void
	{
		$arr = new ArrayList();

		self::assertArrayNotHasKey(0, $arr);
		self::assertArrayNotHasKey(1, $arr);
		self::assertArrayNotHasKey(2, $arr);

		$arr->offsetSet(null, 'test');
		$arr->put(0, 'test2');
		$arr[null] = 'test3';

		self::assertArrayHasKey(0, $arr);
		self::assertArrayHasKey(1, $arr);
		self::assertArrayNotHasKey(2, $arr);
		self::assertSame('test2', $arr[0]);
		self::assertSame('test3', $arr[1]);
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

		self::assertSame('test', $arr->offsetGet(0));
		self::assertSame('test2', $arr[1]);

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

		self::assertCount(0, $arr);

		$arr->add('test');
		$arr[] = 'test2';

		self::assertCount(2, $arr);
		self::assertSame('test', $arr->elementAt(0));
		self::assertSame('test2', $arr->elementAt(1));
	}

	public function testAddAll(): void
	{
		$arr = new ArrayList();

		self::assertCount(0, $arr);

		$arr->addAll(['test', 'test2']);

		self::assertCount(2, $arr);
		self::assertSame('test', $arr->elementAt(0));
		self::assertSame('test2', $arr->elementAt(1));
	}

	public function testIsEmpty(): void
	{
		$filled = new ArrayList(['653', '123', '154']);
		$empty = new ArrayList();

		self::assertFalse($filled->isEmpty());
		self::assertTrue($empty->isEmpty());
	}

	public function testToList(): void
	{
		$arr = new ArrayList(['123', '456']);

		self::assertSame(['123', '456'], $arr->toList());
	}

	public function testFromArray(): void
	{
		$arr = ArrayList::from(['123', '456']);

		self::assertSame('123', $arr->elementAt(0));
		self::assertSame('456', $arr->elementAt(1));
	}

	public function testFromSelf(): void
	{
		$arr = ArrayList::from(['123', '456']);
		$arr2 = ArrayList::from($arr);

		self::assertSame($arr, $arr2);
	}

	public function testFromIterator(): void
	{
		$arr = ArrayList::from(new ArrayIterator(['123', '456']));

		self::assertSame('123', $arr->elementAt(0));
		self::assertSame('456', $arr->elementAt(1));
	}

	public function testFromValue(): void
	{
		$arr = ArrayList::from('123');

		self::assertSame('123', $arr->elementAt(0));
	}

	public function testIterator(): void
	{
		$map = new ArrayList([
			'a' => '1',
		]);

		foreach ($map as $key => $value) {
			self::assertSame('a', $key);
			self::assertSame('1', $value);
		}
	}

	public function testRemoveAt(): void
	{
		$list = new ArrayList([1, 2, 3, 4, 5]);

		self::assertCount(5, $list);

		$removed = $list->removeAt(2);

		self::assertSame(3, $removed);
		self::assertSame(4, $list->count());
		self::assertSame(1, $list->elementAt(0));
		self::assertSame(2, $list->elementAt(1));
		self::assertSame(4, $list->elementAt(2));
		self::assertSame(5, $list->elementAt(3));
	}

	public function testRemove(): void
	{
		$list = new ArrayList([1, 2, 3, 4, 5]);

		self::assertCount(5, $list);

		$removed = $list->removeValue(3);

		self::assertTrue($removed);
		self::assertSame(4, $list->count());
		self::assertSame(1, $list->elementAt(0));
		self::assertSame(2, $list->elementAt(1));
		self::assertSame(4, $list->elementAt(2));
		self::assertSame(5, $list->elementAt(3));
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

		self::assertSame(0, $list->indexOf(1));
		self::assertSame(1, $list->indexOf(2));
		self::assertSame(2, $list->indexOf(3));
		self::assertSame(3, $list->indexOf(4));
		self::assertSame(4, $list->indexOf(5));
		self::assertSame(-1, $list->indexOf(6));
	}

	public function testLastIndexOf(): void
	{
		$list = new ArrayList([1, 2, 3, 4, 5]);

		self::assertSame(4, $list->lastIndexOf(5));
		self::assertSame(3, $list->lastIndexOf(4));
		self::assertSame(2, $list->lastIndexOf(3));
		self::assertSame(1, $list->lastIndexOf(2));
		self::assertSame(0, $list->lastIndexOf(1));
		self::assertSame(-1, $list->lastIndexOf(0));
		self::assertSame(-1, $list->lastIndexOf(7));
	}

	public function testRemoveNotExists(): void
	{
		$list = new ArrayList([1, 2, 3]);

		self::assertFalse($list->removeValue(4));
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

		self::assertSame(3, $list->pop());
		self::assertSame(2, $list->pop());
		self::assertSame(1, $list->pop());
		self::assertSame(0, $list->count());
	}

	public function testPopWithPredicate(): void
	{
		$list = new ArrayList([1, 2, 3]);

		self::assertSame(3, $list->pop(static fn ($item) => $item % 2 === 1));
		self::assertSame(1, $list->pop(static fn ($item) => $item % 2 === 1));
		self::assertSame(1, $list->count());
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

		self::assertSame(1, $list->shift());
		self::assertSame(2, $list->shift());
		self::assertSame(3, $list->shift());
		self::assertSame(0, $list->count());
	}

	public function testShiftWithPredicate(): void
	{
		$list = new ArrayList([1, 2, 3]);

		self::assertSame(1, $list->shift(static fn ($item) => $item % 2 === 1));
		self::assertSame(3, $list->shift(static fn ($item) => $item % 2 === 1));
		self::assertSame(1, $list->count());
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

		self::assertSame(0, $list[0]);
		self::assertSame(1, $list[1]);
	}

	public function testImplode(): void
	{
		$list = new ArrayList([1, 2, 3]);

		self::assertSame('1, 2, 3', $list->implode());
	}

	public function testImplodeWithGlue(): void
	{
		$list = new ArrayList([1, 2, 3]);

		self::assertSame('1-2-3', $list->implode('-'));
	}

	public function testContains(): void
	{
		$list = new ArrayList([1, 2, 3]);

		self::assertTrue($list->contains(1));
		self::assertTrue($list->contains(2));
		self::assertTrue($list->contains(3));
		self::assertFalse($list->contains(4));
	}

	public function testContainsKey(): void
	{
		$list = new ArrayList(['a' => 1, true => 2, 0 => 3]);

		self::assertTrue($list->containsKey('a'));
		self::assertTrue($list->containsKey(true));
		self::assertFalse($list->containsKey(4));
		self::assertFalse($list->containsKey('b'));

		// falsy keys
		self::assertTrue($list->containsKey(0));
		self::assertTrue($list->containsKey(false));
	}

	public function testInsertAt(): void
	{
		$list = new ArrayList(['a', 'b', 'c']);

		$list->insertAt(0, 'z');
		$list->insertAt(2, '-');
		$list->insertAt(5, 'd');

		self::assertCount(6, $list);
		self::assertSame(['z', 'a', '-', 'b', 'c', 'd'], $list->toArray());
	}

	public function testSlice(): void
	{
		$list = new ArrayList(range(0, 5));

		$start = $list->slice(0, 2);
		self::assertCount(2, $start);
		self::assertSame(0, $start[0]);
		self::assertSame(1, $start[1]);

		$end = $list->slice(4, 2);
		self::assertCount(2, $end);
		self::assertSame(4, $end[0]);
		self::assertSame(5, $end[1]);
	}
}
