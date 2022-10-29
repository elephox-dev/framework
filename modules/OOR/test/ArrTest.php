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
 *
 * @uses \Elephox\Collection\IsEnumerable
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
class ArrTest extends TestCase
{
	public function testWrap(): void
	{
		$arr = Arr::wrap(1, 2, 3, 4);
		static::assertSame([1, 2, 3, 4], $arr->getSource());

		$arr2 = Arr::wrap('test');
		static::assertSame(['test'], $arr2->getSource());

		$arr3 = Arr::wrap([1, 3, 4]);
		static::assertSame([1, 3, 4], $arr3->getSource());
	}

	public function testCombine(): void
	{
		static::assertSame(['a' => 1, 'b' => 2], Arr::combine(Arr::wrap('a', 'b'), Arr::wrap(1, 2))->getSource());
		static::assertSame(['a' => 1, 'b' => 2], Arr::combine(['a', 'b'], [1, 2])->getSource());
		static::assertSame(['a' => 1, 'b' => 2], Arr::combine(Arr::wrap('a', 'b'), [1, 2])->getSource());
		static::assertSame(['a' => 1, 'b' => 2], Arr::combine(['a', 'b'], Arr::wrap(1, 2))->getSource());
	}

	public function testRange(): void
	{
		static::assertSame([1, 2, 3, 4, 5], Arr::range(1, 5)->getSource());
		static::assertSame([1, 2, 3, 4, 5], Arr::range(1, 5, 1)->getSource());
		static::assertSame([1, 3, 5], Arr::range(1, 5, 2)->getSource());
		static::assertSame([5, 4, 3, 2, 1], Arr::range(5, 1, -1)->getSource());
		static::assertSame([5, 3, 1], Arr::range(5, 1, -2)->getSource());
	}

	public function testAsEnumerable(): void
	{
		$arr = Arr::wrap(1, 2, 3, 4);
		$enumerable = $arr->asEnumerable();
		static::assertSame([1, 2, 3, 4], $enumerable->toList());
	}

	public function testAsKeyedEnumerable(): void
	{
		$arr = Arr::wrap(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
		$enumerable = $arr->asKeyedEnumerable();
		static::assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], $enumerable->toArray());
	}

	public function testArrayAccess(): void
	{
		$arr = Arr::wrap(1, 2, 3, 4);

		static::assertSame(1, $arr[0]);
		static::assertSame(2, $arr[1]);

		$arr[0] = 5;
		static::assertSame(5, $arr[0]);
		$arr[1] = 6;
		static::assertSame(6, $arr[1]);

		static::assertTrue(isset($arr[0]));
		static::assertFalse(isset($arr[4]));

		unset($arr[0]);

		static::assertFalse(isset($arr[0]));
	}

	public function testChangeKeyCase(): void
	{
		$arr = Arr::wrap(['a' => 1, 'B' => 2, 'c' => 3, 'D' => 4]);
		static::assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], $arr->changeKeyCase()->getSource());
		static::assertSame(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], $arr->changeKeyCase(KeyCase::Lower)->getSource());
		static::assertSame(['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4], $arr->changeKeyCase(KeyCase::Upper)->getSource());
		static::assertSame(['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4], $arr->changeKeyCase(KeyCase::Upper)->getSource());
	}

	public function testChunk(): void
	{
		$arr = Arr::wrap(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
		static::assertSame([[1, 2, 3, 4, 5], [6, 7, 8, 9, 10]], $arr->chunk(5)->getSource());
		static::assertSame([[1, 2, 3, 4, 5], [6, 7, 8, 9, 10]], $arr->chunk(5, false)->getSource());
		static::assertSame([[1, 2, 3, 4, 5], [5 => 6, 6 => 7, 7 => 8, 8 => 9, 9 => 10]], $arr->chunk(5, true)->getSource());
	}

	public function testDiff(): void
	{
		$arr = Arr::range(1, 10);
		static::assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $arr->diff([])->getSource());
		static::assertSame([], $arr->diff([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])->getSource());
		static::assertSame([9 => 10], $arr->diff([1, 2, 3, 4, 5, 6, 7, 8, 9, 11])->getSource());

		$arr2 = Arr::wrap(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]);
		static::assertSame(['c' => 3, 'd' => 4], $arr2->diff(['a' => 5], Diff::Key, ['b' => 6])->getSource());
		static::assertSame(['a' => 1, 'b' => 2, 'd' => 4], $arr2->diff(['c' => 3, 'e' => 5], Diff::Assoc)->getSource());
		static::assertSame(['a' => 1, 'b' => 2], $arr2->diff(['c' => 3, 'e' => 5], Diff::Normal, ['g' => 4])->getSource());
	}
}
