<?php
declare(strict_types=1);

namespace Philly\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Philly\Collection\ArrayList
 * @covers \Philly\Collection\OffsetNotAllowedException
 * @covers \Philly\Collection\OffsetNotFoundException
 * @covers \Philly\Collection\InvalidOffsetException
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

		self::assertArrayNotHasKey(10, $arr);
		self::assertArrayNotHasKey(90, $arr);
		self::assertArrayNotHasKey(17, $arr);

		$arr->offsetSet(10, "test");
		$arr->set(90, "test2");
		$arr[17] = "test2";

		self::assertArrayHasKey(10, $arr);
		self::assertArrayHasKey(90, $arr);
		self::assertArrayHasKey(17, $arr);

		$this->expectException(OffsetNotAllowedException::class);

		$arr->offsetSet("not a number", "test");
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
}
