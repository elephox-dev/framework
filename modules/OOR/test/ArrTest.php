<?php
declare(strict_types=1);

namespace Elephox\OOR;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\OOR\Arr
 * @covers \Elephox\Collection\Enumerable
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\OOR\KeyCase
 * @uses \Elephox\Collection\IsEnumerable
 * @uses \Elephox\Collection\IsKeyedEnumerable
 */
class ArrTest extends TestCase
{
	public function testWrap(): void
	{
		$arr = Arr::wrap(1, 2, 3, 4);
		self::assertEquals([1, 2, 3, 4], $arr->getSource());

		$arr2 = Arr::wrap('test');
		self::assertEquals(['test'], $arr2->getSource());

		$arr3 = Arr::wrap([1, 3, 4]);
		self::assertEquals([1, 3, 4], $arr3->getSource());
	}

	public function testCombine(): void
	{
		self::assertEquals(['a' => 1, 'b' => 2], Arr::combine(Arr::wrap('a', 'b'), Arr::wrap(1, 2))->getSource());
		self::assertEquals(['a' => 1, 'b' => 2], Arr::combine(['a', 'b'], [1, 2])->getSource());
		self::assertEquals(['a' => 1, 'b' => 2], Arr::combine(Arr::wrap('a', 'b'), [1, 2])->getSource());
		self::assertEquals(['a' => 1, 'b' => 2], Arr::combine(['a', 'b'], Arr::wrap(1, 2))->getSource());
	}

	public function testRange(): void
	{
		self::assertEquals([1, 2, 3, 4, 5], Arr::range(1, 5)->getSource());
		self::assertEquals([1, 2, 3, 4, 5], Arr::range(1, 5, 1)->getSource());
		self::assertEquals([1, 3, 5], Arr::range(1, 5, 2)->getSource());
		self::assertEquals([5, 4, 3, 2, 1], Arr::range(5, 1, -1)->getSource());
		self::assertEquals([5, 3, 1], Arr::range(5, 1, -2)->getSource());
	}

	public function testAsEnumerable(): void
	{
		$arr = Arr::wrap(1, 2, 3, 4);
		$enumerable = $arr->asEnumerable();
		self::assertEquals([1, 2, 3, 4], $enumerable->toList());
	}

	public function testAsKeyedEnumerable(): void
	{
		$arr = Arr::wrap(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
		$enumerable = $arr->asKeyedEnumerable();
		self::assertEquals(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], $enumerable->toArray());
	}

	public function testArrayAccess(): void
	{
		$arr = Arr::wrap(1, 2, 3, 4);

		self::assertEquals(1, $arr[0]);
		self::assertEquals(2, $arr[1]);

		$arr[0] = 5;
		self::assertEquals(5, $arr[0]);
		$arr[1] = 6;
		self::assertEquals(6, $arr[1]);

		self::assertTrue(isset($arr[0]));
		self::assertFalse(isset($arr[4]));

		unset($arr[0]);

		self::assertFalse(isset($arr[0]));
	}

	public function testChangeKeyCase(): void
	{
		$arr = Arr::wrap(['a' => 1, 'B' => 2, 'c' => 3, 'D' => 4]);
		self::assertEquals(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], $arr->changeKeyCase()->getSource());
		self::assertEquals(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], $arr->changeKeyCase(KeyCase::Lower)->getSource());
		self::assertEquals(['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4], $arr->changeKeyCase(KeyCase::Upper)->getSource());
		self::assertEquals(['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4], $arr->changeKeyCase(KeyCase::Upper)->getSource());
	}

	public function testChunk(): void
	{
		$arr = Arr::wrap(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
		self::assertEquals([[1, 2, 3, 4, 5], [6, 7, 8, 9, 10]], $arr->chunk(5)->getSource());
		self::assertEquals([[1, 2, 3, 4, 5], [6, 7, 8, 9, 10]], $arr->chunk(5, false)->getSource());
		self::assertEquals([[1, 2, 3, 4, 5], [5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10]], $arr->chunk(5, true)->getSource());
	}
}
