<?php
declare(strict_types=1);

namespace Elephox\OOR;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\OOR\Arr
 * @covers \Elephox\Collection\Enumerable
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\OOR\KeyCase
 * @covers \Elephox\Collection\IteratorProvider
 * @covers \Elephox\OOR\Diff
 *
 * @uses \Elephox\Collection\IsEnumerable
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
final class ArrTest extends TestCase
{
	public function testWrap(): void
	{
		$arr = Arr::wrap(1, 2, 3, 4);
		self::assertSame([1, 2, 3, 4], $arr->getSource());

		$arr2 = Arr::wrap('test');
		self::assertSame(['test'], $arr2->getSource());

		$arr3 = arr(1, 3, 4);
		self::assertSame([1, 3, 4], $arr3->getSource());
	}

	public function testCombine(): void
	{
		self::assertSame(['a' => 1, 'b' => 2], Arr::combine(Arr::wrap('a', 'b'), Arr::wrap(1, 2))->getSource());
		self::assertSame(['a' => 1, 'b' => 2], Arr::combine(['a', 'b'], [1, 2])->getSource());
		self::assertSame(['a' => 1, 'b' => 2], Arr::combine(Arr::wrap('a', 'b'), [1, 2])->getSource());
		self::assertSame(['a' => 1, 'b' => 2], Arr::combine(['a', 'b'], Arr::wrap(1, 2))->getSource());
	}

	public function testRange(): void
	{
		self::assertSame([1, 2, 3, 4, 5], Arr::range(1, 5)->getSource());
		self::assertSame([1, 2, 3, 4, 5], Arr::range(1, 5, 1)->getSource());
		self::assertSame([1, 3, 5], Arr::range(1, 5, 2)->getSource());
		self::assertSame([5, 4, 3, 2, 1], Arr::range(5, 1, -1)->getSource());
		self::assertSame([5, 3, 1], Arr::range(5, 1, -2)->getSource());
	}

	public function testAsEnumerable(): void
	{
		$arr = Arr::wrap(1, 2, 3, 4);
		$enumerable = $arr->asEnumerable();
		self::assertSame([1, 2, 3, 4], $enumerable->toList());
	}

	public function testAsKeyedEnumerable(): void
	{
		$arr = Arr::wrap(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
		$enumerable = $arr->asKeyedEnumerable();
		self::assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], $enumerable->toArray());
	}

	public function testArrayAccess(): void
	{
		$arr = Arr::wrap(1, 2, 3, 4);

		self::assertSame(1, $arr[0]);
		self::assertSame(2, $arr[1]);

		$arr[0] = 5;
		self::assertSame(5, $arr[0]);
		$arr[1] = 6;
		self::assertSame(6, $arr[1]);

		self::assertTrue(isset($arr[0]));
		self::assertFalse(isset($arr[4]));

		unset($arr[0]);

		self::assertFalse(isset($arr[0]));
	}

	public function testChangeKeyCase(): void
	{
		$arr = Arr::wrap(['a' => 1, 'B' => 2, 'c' => 3, 'D' => 4]);
		self::assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], $arr->changeKeyCase()->getSource());
		self::assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], $arr->changeKeyCase(KeyCase::Lower)->getSource());
		self::assertSame(['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4], $arr->changeKeyCase(KeyCase::Upper)->getSource());
		self::assertSame(['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4], $arr->changeKeyCase(KeyCase::Upper)->getSource());
	}

	public function testChunk(): void
	{
		$arr = Arr::wrap(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
		self::assertSame([[1, 2, 3, 4, 5], [6, 7, 8, 9, 10]], $arr->chunk(5)->getSource());
		self::assertSame([[1, 2, 3, 4, 5], [6, 7, 8, 9, 10]], $arr->chunk(5, false)->getSource());
		self::assertSame([[1, 2, 3, 4, 5], [5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10]], $arr->chunk(5, true)->getSource());
	}

	public function testDiff(): void
	{
		$arr = Arr::range(1, 10);
		self::assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $arr->diff([])->getSource());
		self::assertSame([], $arr->diff([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])->getSource());
		self::assertSame([9 => 10], $arr->diff([1, 2, 3, 4, 5, 6, 7, 8, 9, 11])->getSource());

		$arr2 = Arr::wrap(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
		self::assertSame(['c' => 3, 'd' => 4], $arr2->diff(['a' => 5], Diff::Key, ['b' => 6])->getSource());
		self::assertSame(['a' => 1, 'b' => 2, 'd' => 4], $arr2->diff(['c' => 3, 'e' => 5], Diff::Assoc)->getSource());
		self::assertSame(['a' => 1, 'b' => 2], $arr2->diff(['c' => 3, 'e' => 5], Diff::Normal, ['g' => 4])->getSource());
	}
}
