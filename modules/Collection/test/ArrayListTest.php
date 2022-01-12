<?php
declare(strict_types=1);

namespace Elephox\Collection;

use Elephox\Collection\Contract\GenericList;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Elephox\Collection\ArrayList
 * @covers \Elephox\Collection\OffsetNotAllowedException
 * @covers \Elephox\Collection\OffsetNotFoundException
 * @covers \Elephox\Collection\InvalidOffsetException
 */
class ArrayListTest extends TestCase
{
	public function testOffsetExists(): void
	{
		$arr = new ArrayList(["test"]);

		self::assertTrue($arr->offsetExists(0));
		self::assertArrayHasKey(0, $arr);
		self::assertFalse($arr->offsetExists(1));
	}

	public function testCount(): void
	{
		$arr = new ArrayList(["test", "test2", "test3"]);

		self::assertCount(3, $arr);
	}

	public function testOffsetUnset(): void
	{
		$arr = new ArrayList(["test", "test2", "test3"]);

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

		$arr->offsetSet(null, "test");
		$arr->put(0, "test2");
		$arr[null] = "test3";

		self::assertArrayHasKey(0, $arr);
		self::assertArrayHasKey(1, $arr);
		self::assertArrayNotHasKey(2, $arr);
		self::assertEquals("test2", $arr[0]);
		self::assertEquals("test3", $arr[1]);
	}

	public function testOffsetSetInvalidType(): void
	{
		$arr = new ArrayList();

		$this->expectException(OffsetNotAllowedException::class);

		$arr->offsetSet("not a number", "test");
	}

	public function testOffsetSetOutsideOfArray(): void
	{
		$arr = new ArrayList();

		$this->expectException(InvalidArgumentException::class);

		$arr->offsetSet(10, "test");
	}

	public function testOffsetGet(): void
	{
		$arr = new ArrayList(["test", "test2", "test3"]);

		self::assertEquals("test", $arr->offsetGet(0));
		self::assertEquals("test2", $arr[1]);

		$this->expectException(OffsetNotAllowedException::class);
		$this->expectExceptionMessage("Offset 'not a number' is not allowed.");

		$arr->offsetGet("not a number");
	}

	public function testElementAt(): void
	{
		$arr = new ArrayList(["test", "test2"]);

		$this->expectException(OffsetNotFoundException::class);
		$this->expectExceptionMessage("Offset '123' does not exist.");

		$arr->elementAt(123);
	}

	public function testAdd(): void
	{
		$arr = new ArrayList();

		self::assertCount(0, $arr);

		$arr->add("test");
		$arr[] = "test2";

		self::assertCount(2, $arr);
		self::assertEquals("test", $arr->elementAt(0));
		self::assertEquals("test2", $arr->elementAt(1));
	}

	public function testAddAll(): void
	{
		$arr = new ArrayList();

		self::assertCount(0, $arr);

		$arr->addAll(["test", "test2"]);

		self::assertCount(2, $arr);
		self::assertEquals("test", $arr->elementAt(0));
		self::assertEquals("test2", $arr->elementAt(1));
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

		self::assertEquals(['123', '456'], $arr->toList());
	}

	public function testFromArray(): void
	{
		$arr = ArrayList::from(['123', '456']);

		self::assertEquals('123', $arr->elementAt(0));
		self::assertEquals('456', $arr->elementAt(1));
	}

	public function testIterator(): void
	{
		$map = new ArrayList([
			'a' => '1',
		]);

		foreach ($map as $value) {
			self::assertEquals('1', $value);
		}
	}

	public function testDeepClone(): void
	{
		$anObject = new stdClass();
		$anObject->test = true;
		$list = new ArrayList([$anObject, 2, $anObject]);
		$clone = $list->deepClone();

		self::assertInstanceOf(ArrayList::class, $clone);
		self::assertNotSame($list, $clone);
		self::assertNotSame($list[0], $clone[0]);
		self::assertNotSame($list[2], $clone[2]);
		self::assertSame($clone[0], $clone[2]);
	}

	public function testRemove(): void
	{
		$list = new ArrayList([1, 2, 3, 4, 5]);

		self::assertEquals(5, $list->count());

		$list->removeAt(2);

		self::assertEquals(4, $list->count());
		self::assertEquals(1, $list->elementAt(0));
		self::assertEquals(2, $list->elementAt(1));
		self::assertEquals(4, $list->elementAt(2));
		self::assertEquals(5, $list->elementAt(3));
	}
}
