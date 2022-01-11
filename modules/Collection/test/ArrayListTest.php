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
		$arr->set(0, "test2");
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
		self::assertEquals("test", $arr->get(0));
		self::assertEquals("test", $arr[0]);

		$this->expectException(OffsetNotAllowedException::class);
		$this->expectExceptionMessage("Offset 'not a number' is not allowed.");

		$arr->offsetGet("not a number");
	}

	public function testGet(): void
	{
		$arr = new ArrayList(["test", "test2"]);

		$this->expectException(OffsetNotFoundException::class);
		$this->expectExceptionMessage("Offset '123' does not exist.");

		$arr->get(123);
	}

	public function testAdd(): void
	{
		$arr = new ArrayList();

		self::assertCount(0, $arr);

		$arr->add("test");
		$arr[] = "test2";

		self::assertCount(2, $arr);
		self::assertEquals("test", $arr->get(0));
		self::assertEquals("test2", $arr->get(1));
	}

	public function testAddAll(): void
	{
		$arr = new ArrayList();

		self::assertCount(0, $arr);

		$arr->addAll(["test", "test2"]);

		self::assertCount(2, $arr);
		self::assertEquals("test", $arr->get(0));
		self::assertEquals("test2", $arr->get(1));
	}

	public function testFirst(): void
	{
		$arr = new ArrayList(['653', '123', '1543']);

		self::assertEquals("123", $arr->first(fn(string $a) => $a[0] === '1'));
		self::assertNull($arr->first(fn(string $a) => $a[0] === '4'));
	}

	public function testWhere(): void
	{
		$arr = new ArrayList(['653', '123', '154']);
		$res = $arr->where(fn(string $a) => str_ends_with($a, '3'));

		self::assertCount(2, $res);
		self::assertEquals('653', $res[0]);
	}

	public function testIsEmpty(): void
	{
		$filled = new ArrayList(['653', '123', '154']);
		$empty = new ArrayList();

		self::assertFalse($filled->isEmpty());
		self::assertTrue($empty->isEmpty());
	}

	public function testAsArray(): void
	{
		$arr = new ArrayList(['123', '456']);

		self::assertEquals(['123', '456'], $arr->asArray());
	}

	public function testMap(): void
	{
		$arr = new ArrayList([123]);

		$stringArr = $arr->map(fn(int $a) => (string)$a);

		self::assertEquals('123', $stringArr->get(0));
	}

	public function testAny(): void
	{
		$arr = new ArrayList([123, 456]);

		self::assertTrue($arr->any(fn(int $a) => $a > 400));
		self::assertFalse($arr->any(fn(int $a) => $a < 100));
	}

	public function testFromArray(): void
	{
		$arr = ArrayList::fromArray(['123', '456']);

		self::assertEquals('123', $arr->get(0));
		self::assertEquals('456', $arr->get(1));
	}

	public function testPush(): void
	{
		$arr = new ArrayList(['435']);

		self::assertCount(1, $arr);

		$arr->push('test');

		self::assertCount(2, $arr);
		self::assertEquals('test', $arr->get(1));
	}

	public function testPopPeek(): void
	{
		$arr = new ArrayList(['123', '456']);

		self::assertCount(2, $arr);

		$peeked = $arr->peek();

		self::assertCount(2, $arr);

		$popped = $arr->pop();

		self::assertCount(1, $arr);
		self::assertEquals('456', $popped);
		self::assertEquals($popped, $peeked);
	}

	public function testSinglePeek(): void
	{
		$arr = new ArrayList(['123']);

		self::assertCount(1, $arr);

		$peeked = $arr->peek();

		self::assertCount(1, $arr);
		self::assertEquals('123', $peeked);
	}

	public function testInvalidPeek(): void
	{
		$this->expectException(OffsetNotFoundException::class);
		$this->expectExceptionMessage("Offset '0' does not exist.");

		(new ArrayList())->peek();
	}

	public function testShift(): void
	{
		$arr = new ArrayList([123, 456, 789]);

		self::assertCount(3, $arr);

		$shifted = $arr->shift();

		self::assertCount(2, $arr);
		self::assertEquals(123, $shifted);
	}

	public function testInvalidShift(): void
	{
		$this->expectException(OffsetNotFoundException::class);
		$this->expectExceptionMessage("Offset '0' does not exist.");

		(new ArrayList())->shift();
	}

	public function testUnshift(): void
	{
		$arr = new ArrayList([456, 789]);

		self::assertCount(2, $arr);

		$arr->unshift(123);

		self::assertCount(3, $arr);
		self::assertEquals(123, $arr->get(0));
	}

	public function testContains(): void
	{
		$map = new ArrayList([
			'a' => '1',
			'b' => '2',
			'c' => '3',
		]);

		self::assertTrue($map->contains('1'));
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

	public function testOrderBy(): void
	{
		$map = new ArrayList([3, 1, 2]);

		$sorted = $map->orderBy(fn(int $a, int $b) => $a - $b);

		self::assertEquals(1, $sorted->get(0));
		self::assertEquals(2, $sorted->get(1));
		self::assertEquals(3, $sorted->get(2));
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
}
