<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\Enumerable
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\Collection\Iterator\RangeIterator
 * @covers \Elephox\Collection\Iterator\SelectIterator
 * @covers \Elephox\Collection\Iterator\KeySelectIterator
 * @covers \Elephox\Collection\Iterator\WhileIterator
 * @covers \Elephox\Collection\Iterator\ReverseIterator
 * @covers \Elephox\Collection\Iterator\UniqueByIterator
 * @covers \Elephox\Collection\Iterator\FlipIterator
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\EmptySequenceException
 * @covers \Elephox\Collection\AmbiguousMatchException
 * @covers \Elephox\Collection\Iterator\OrderedIterator
 * @covers \Elephox\Collection\OrderedEnumerable
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
class KeyedEnumerableTest extends TestCase
{
	public function testFromString(): void
	{
		static::assertEquals(
			['a'],
			KeyedEnumerable::from('a')->toList(),
		);
	}

	public function testFromIterator(): void
	{
		static::assertEquals(
			['a' => 1, 'b' => 2, 'c' => 3],
			KeyedEnumerable::from(new ArrayIterator(['a' => 1, 'b' => 2, 'c' => 3]))->toArray(),
		);
	}

	public function testFromSelf(): void
	{
		$keyedEnumerable = KeyedEnumerable::from(['a', 'b', 'c']);

		static::assertSame(
			$keyedEnumerable,
			KeyedEnumerable::from($keyedEnumerable),
		);
	}

	public function testFromThrows(): void
	{
		$this->expectException(InvalidArgumentException::class);
		KeyedEnumerable::from(null);
	}

	public function testConstructorClosureThrows(): void
	{
		$this->expectException(InvalidArgumentException::class);
		new KeyedEnumerable(static fn () => null);
	}

	public function testAggregate(): void
	{
		static::assertEquals(
			120,
			KeyedEnumerable::range(1, 5)->aggregate(static fn ($a, $b) => $a * $b, 1),
		);

		static::assertEquals(
			'abc',
			KeyedEnumerable::from(['a', 'b', 'c'])->aggregate(static fn ($a, $b) => $a . $b),
		);
	}

	public function testAll(): void
	{
		static::assertTrue(KeyedEnumerable::range(1, 5)->all(static fn ($x) => $x < 6));
		static::assertFalse(KeyedEnumerable::range(1, 5)->all(static fn ($x) => $x < 4));
	}

	public function testAny(): void
	{
		static::assertTrue(KeyedEnumerable::range(1, 3)->any());
		static::assertFalse(KeyedEnumerable::empty()->any());
		static::assertTrue(KeyedEnumerable::range(1, 3)->any(static fn ($x) => $x > 1));
		static::assertFalse(KeyedEnumerable::range(1, 3)->any(static fn ($x) => $x > 4));
	}

	public function testAppendKeyed(): void
	{
		static::assertEquals(
			[1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd', 5 => 'e'],
			KeyedEnumerable::from([1 => 'a', 2 => 'b', 3 => 'c'])->append(4, 'd')->append(5, 'e')->toArray(),
		);
	}

	public function testAverage(): void
	{
		static::assertEquals(2, KeyedEnumerable::range(1, 3)->average(static fn (int $x) => $x));
	}

	public function testChunk(): void
	{
		static::assertEquals(
			[
				[1, 2, 3],
				[4, 5, 6],
				[7, 8, 9],
			],
			KeyedEnumerable::range(1, 9)->chunk(3)->toList(),
		);
	}

	public function testConcat(): void
	{
		static::assertEquals(
			[1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
			KeyedEnumerable::range(1, 3)
				->concat(KeyedEnumerable::range(4, 7), KeyedEnumerable::range(8, 10))
				->toList(),
		);
	}

	public function testContains(): void
	{
		static::assertTrue(KeyedEnumerable::range(1, 10)->contains(5));
		static::assertFalse(KeyedEnumerable::range(1, 10)->contains(11));
	}

	public function testCount(): void
	{
		static::assertEquals(10, KeyedEnumerable::range(1, 10)->count());
		static::assertEquals(5, KeyedEnumerable::range(1, 10)->count(static fn (int $x): bool => $x % 2 === 0));
	}

	public function testDistinct(): void
	{
		static::assertEquals(
			[1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
			KeyedEnumerable::range(1, 10)->distinct()->toList(),
		);

		static::assertEquals(
			[1, 3, 2],
			KeyedEnumerable::from([1, 1, 3, 2, 3, 1, 2, 3])->distinct()->toList(),
		);
	}

	public function testDistinctBy(): void
	{
		static::assertEquals(
			[1, 2, 3],
			KeyedEnumerable::range(1, 10)->distinctBy(static fn (int $x): int => $x % 3)->toList(),
		);

		static::assertEquals(
			[1, 3, 2],
			KeyedEnumerable::from([1, 1, 3, 2, 3, 1, 2, 3])->distinctBy(static fn (int $x): int => $x % 3)->toList(),
		);
	}

	public function testExcept(): void
	{
		static::assertEquals(
			[1, 2, 7, 8, 9, 10],
			KeyedEnumerable::range(1, 10)->except(KeyedEnumerable::range(3, 6))->toList(),
		);
	}

	public function testExceptBy(): void
	{
		static::assertEquals(
			[
				['name' => 'alice', 'age' => 5],
				['name' => 'charlie', 'age' => 4],
			],
			KeyedEnumerable::from([
				['name' => 'alice', 'age' => 5],
				['name' => 'bob', 'age' => 10],
				['name' => 'charlie', 'age' => 4],
			])
				->exceptBy(
					KeyedEnumerable::from([
						['age' => 10],
					]),
					static fn (array $x): int => $x['age'],
				)
				->toList(),
		);
	}

	public function testFirst(): void
	{
		static::assertEquals(1, KeyedEnumerable::range(1, 10)->first());
		static::assertEquals(2, KeyedEnumerable::range(1, 10)->first(static fn (int $x): bool => $x % 2 === 0));

		$this->expectException(EmptySequenceException::class);
		KeyedEnumerable::empty()->first();
	}

	public function testFirstOrDefault(): void
	{
		static::assertEquals(1, KeyedEnumerable::range(1, 10)->firstOrDefault(null));
		static::assertEquals(2, KeyedEnumerable::range(1, 10)->firstOrDefault(null, static fn (int $x): bool => $x % 2 === 0));
		static::assertNull(KeyedEnumerable::empty()->firstOrDefault(null));
	}

	public function testFlip(): void
	{
		static::assertEquals(
			[
				'a' => 0,
				'b' => 1,
				'c' => 2,
			],
			KeyedEnumerable::from(['a', 'b', 'c'])->flip()->toArray(),
		);
	}

	public function testIntersect(): void
	{
		static::assertEquals(
			[3, 4, 5],
			KeyedEnumerable::range(1, 5)->intersect(KeyedEnumerable::range(3, 8))->toList(),
		);
	}

	public function testIntersectBy(): void
	{
		static::assertEquals(
			[
				['name' => 'bob', 'age' => 10],
			],
			KeyedEnumerable::from([
				['name' => 'alice', 'age' => 5],
				['name' => 'bob', 'age' => 10],
				['name' => 'charlie', 'age' => 4],
			])
				->intersectBy(
					KeyedEnumerable::from([
						['age' => 10],
					]),
					static fn (array $x): int => $x['age'],
				)
				->toList(),
		);
	}

	public function testIsEmpty(): void
	{
		static::assertTrue(KeyedEnumerable::empty()->isEmpty());
	}

	public function testJoin(): void
	{
		static::assertEquals(
			[2, 4, 6, 8, 10],
			KeyedEnumerable::range(1, 5)->join(
				KeyedEnumerable::range(1, 5),
				static fn (int $a) => $a,
				static fn (int $b) => $b,
				static fn (int $a, int $b) => $a + $b,
			)->toList(),
		);
	}

	public function testLast(): void
	{
		static::assertEquals(
			'c',
			KeyedEnumerable::from(['a', 'b', 'c'])->last(),
		);
	}

	public function testLastOrDefault(): void
	{
		static::assertEquals(3, KeyedEnumerable::from([1, 2, 3])->lastOrDefault(null));
		static::assertNull(KeyedEnumerable::empty()->lastOrDefault(null));
	}

	public function testMax(): void
	{
		static::assertEquals(
			10,
			KeyedEnumerable::range(1, 10)->max(static fn (int $x) => $x),
		);
	}

	public function testMin(): void
	{
		static::assertEquals(
			1,
			KeyedEnumerable::range(1, 3)->min(static fn (int $x) => $x),
		);
	}

	public function testOrderBy(): void
	{
		static::assertEquals(
			[1, 2, 3, 4, 5, 6],
			KeyedEnumerable::from([6, 2, 5, 1, 4, 3])->orderBy(static fn (int $x) => $x)->toList(),
		);
	}

	public function testOrderByDescending(): void
	{
		static::assertEquals(
			[
				[
					'name' => 'b',
					'age' => 2,
				],
				[
					'name' => 'a',
					'age' => 1,
				],
			],
			KeyedEnumerable::from([
				['name' => 'a', 'age' => 1],
				['name' => 'b', 'age' => 2],
			])->orderByDescending(static fn ($x) => $x['age'])->toList(),
		);
	}

	public function testPrependKeyed(): void
	{
		static::assertEquals(
			['e' => 5, 'd' => 4, 'a' => 1, 'b' => 2, 'c' => 3],
			KeyedEnumerable::from(['a' => 1, 'b' => 2, 'c' => 3])->prepend('d', 4)->prepend('e', 5)->toArray(),
		);
	}

	public function testReverse(): void
	{
		static::assertEquals(
			[5, 4, 3, 2, 1],
			KeyedEnumerable::range(1, 5)->reverse()->toArray(),
		);
	}

	public function testSelect(): void
	{
		static::assertEquals(
			[2, 4, 6, 8, 10],
			KeyedEnumerable::range(1, 5)
				->select(static fn (int $x): int => $x * 2)
				->toList(),
		);
	}

	public function testSelectMany(): void
	{
		static::assertEquals(
			[
				1,
				1, 2,
				1, 2, 3,
				1, 2, 3, 4,
				1, 2, 3, 4, 5,
			],
			KeyedEnumerable::range(1, 5)
				->selectMany(static fn (int $x): GenericKeyedEnumerable => KeyedEnumerable::range(1, $x))
				->toList(),
		);
	}

	public function testSequenceEqual(): void
	{
		static::assertTrue(
			KeyedEnumerable::range(1, 5)->sequenceEqual(KeyedEnumerable::range(1, 5)),
		);

		static::assertFalse(
			KeyedEnumerable::range(1, 5)->sequenceEqual(KeyedEnumerable::range(1, 6)),
		);

		static::assertTrue(KeyedEnumerable::empty()->sequenceEqual(KeyedEnumerable::empty()));
	}

	public function testSingle(): void
	{
		static::assertEquals(
			2,
			KeyedEnumerable::from([2])->single(),
		);
	}

	public function testSingleMultipleElements(): void
	{
		$this->expectException(AmbiguousMatchException::class);
		KeyedEnumerable::from([1, 2])->single();
	}

	public function testSingleNoElements(): void
	{
		$this->expectException(EmptySequenceException::class);
		KeyedEnumerable::empty()->single();
	}

	public function testSingleOrDefault(): void
	{
		static::assertEquals(
			1,
			KeyedEnumerable::range(1, 5)->singleOrDefault(null, static fn (int $x): bool => $x === 1),
		);

		static::assertNull(
			KeyedEnumerable::range(1, 5)->singleOrDefault(null, static fn (int $x): bool => $x === 6),
		);
	}

	public function testSkip(): void
	{
		static::assertEquals(
			[3, 4, 5],
			KeyedEnumerable::range(1, 5)
				->skip(2)
				->toList(),
		);
	}

	public function testSkipLast(): void
	{
		static::assertEquals(
			[1, 2, 3],
			KeyedEnumerable::range(1, 5)
				->skipLast(2)
				->toList(),
		);
	}

	public function testSkipWhile(): void
	{
		static::assertEquals(
			[3, 4, 5],
			KeyedEnumerable::range(1, 5)
				->skipWhile(static fn (int $x): bool => $x < 3)
				->toList(),
		);
	}

	public function testTake(): void
	{
		static::assertEquals(
			[0, 1, 2],
			KeyedEnumerable::range(0, 6)->take(3)->toList(),
		);
	}

	public function testTakeLast(): void
	{
		static::assertEquals(
			[5, 6],
			KeyedEnumerable::range(0, 6)->takeLast(2)->toList(),
		);
	}

	public function testTakeLastInvalid(): void
	{
		static::assertEquals(
			[],
			KeyedEnumerable::range(0, 6)->takeLast(-2)->toList(),
		);
	}

	public function testTakeLastEmpty(): void
	{
		static::assertEquals(
			[],
			KeyedEnumerable::empty()->takeLast(1)->toList(),
		);
	}

	public function testTakeWhile(): void
	{
		static::assertEquals(
			[0, 1, 2],
			KeyedEnumerable::range(0, 6)->takeWhile(static fn (int $x): bool => $x < 3)->toList(),
		);
	}

	public function testUnion(): void
	{
		$a = KeyedEnumerable::from([5, 3, 9, 7, 5, 9, 3, 7]);
		$b = KeyedEnumerable::from([8, 3, 6, 4, 4, 9, 1, 0]);

		static::assertEquals(
			[5, 3, 9, 7, 8, 6, 4, 1, 0],
			$a->union($b)->toList(),
		);
	}

	public function testUnionBy(): void
	{
		$a = KeyedEnumerable::from([5, 3, 9, 7, 5, 9, 3, 7]);
		$b = KeyedEnumerable::from([8, 3, 6, 4, 4, 9, 1, 0]);

		static::assertEquals(
			[5, 3, 9, 7, 6],
			$a->unionBy($b, static fn (int $a) => $a % 5)->toList(),
		);
	}

	public function testWhereKey(): void
	{
		static::assertEquals(
			['b' => 2, 'c' => 3],
			KeyedEnumerable::from(['a' => 1, 'b' => 2, 'c' => 3])->whereKey(static fn ($x) => $x > 'a')->toArray(),
		);
	}

	public function testZip(): void
	{
		static::assertEquals(
			[
				[1, 4],
				[2, 5],
				[3, 6],
			],
			KeyedEnumerable::range(1, 3)->zip(KeyedEnumerable::range(4, 6))->toList(),
		);
	}
}
