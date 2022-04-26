<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Iterator\EagerCachingIterator;
use EmptyIterator;
use Exception;
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
 * @covers \Elephox\Collection\Iterator\EagerCachingIterator
 * @covers \Elephox\Collection\IteratorProvider
 * @covers \Elephox\Collection\Iterator\GroupingIterator
 * @covers \Elephox\Collection\Grouping
 * @covers \Elephox\Collection\GroupedKeyedEnumerable
 *
 * @uses \Elephox\Collection\IsEnumerable
 *
 * @internal
 */
class EnumerableTest extends TestCase
{
	public function testFromString(): void
	{
		static::assertEquals(
			['a'],
			Enumerable::from('a')->toList(),
		);
	}

	public function testFromIterator(): void
	{
		static::assertEquals(
			['a', 'b', 'c'],
			Enumerable::from(new ArrayIterator(['a', 'b', 'c']))->toList(),
		);
	}

	public function testFromSelf(): void
	{
		$keyedEnumerable = Enumerable::from(['a', 'b', 'c']);

		static::assertSame(
			$keyedEnumerable,
			Enumerable::from($keyedEnumerable),
		);
	}

	public function testFromThrows(): void
	{
		$this->expectException(InvalidArgumentException::class);
		Enumerable::from(null);
	}

	public function testGetIteratorThrowsForInvalidClosure(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Given iterator generator does not return an iterator');

		$enum = new Enumerable(static fn () => null);
		$enum->getIterator();
	}

	public function testAggregate(): void
	{
		static::assertEquals(
			120,
			Enumerable::range(1, 5)->aggregate(static fn ($a, $b) => $a * $b, 1),
		);

		static::assertEquals(
			'abc',
			Enumerable::from(['a', 'b', 'c'])->aggregate(static fn ($a, $b) => $a . $b),
		);
	}

	public function testAll(): void
	{
		static::assertTrue(Enumerable::range(1, 5)->all(static fn ($x) => $x < 6));
		static::assertFalse(Enumerable::range(1, 5)->all(static fn ($x) => $x < 4));
	}

	public function testAny(): void
	{
		static::assertTrue(Enumerable::range(1, 3)->any());
		static::assertFalse(Enumerable::empty()->any());
		static::assertTrue(Enumerable::range(1, 3)->any(static fn ($x) => $x > 1));
		static::assertFalse(Enumerable::range(1, 3)->any(static fn ($x) => $x > 4));
	}

	public function testAppend(): void
	{
		static::assertEquals(
			[1, 2, 3, 4, 5],
			Enumerable::range(1, 3)->append(4)->append(5)->toArray(),
		);
	}

	public function testAppendAll(): void
	{
		static::assertEquals(
			[1, 2, 3, 4, 5],
			Enumerable::range(1, 3)->appendAll([4, 5])->toArray(),
		);
	}

	public function testAverage(): void
	{
		static::assertEquals(2, Enumerable::range(1, 3)->average(static fn (int $x) => $x));
	}

	public function testAverageThrowsIfEmpty(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		Enumerable::empty()->average(static fn (int $x) => $x);
	}

	public function testChunk(): void
	{
		static::assertEquals(
			[
				[1, 2, 3],
				[4, 5, 6],
				[7, 8, 9],
			],
			Enumerable::range(1, 9)->chunk(3)->toList(),
		);
	}

	public function testChunkInvalidSize(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Chunk size must be greater than zero');

		Enumerable::range(1, 3)->chunk(0);
	}

	public function testConcat(): void
	{
		static::assertEquals(
			[1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
			Enumerable::range(1, 3)
				->concat(Enumerable::range(4, 7), Enumerable::range(8, 10))
				->toList(),
		);
	}

	public function testContains(): void
	{
		static::assertTrue(Enumerable::range(1, 10)->contains(5));
		static::assertFalse(Enumerable::range(1, 10)->contains(11));
	}

	public function testCount(): void
	{
		static::assertEquals(10, Enumerable::range(1, 10)->count());
		static::assertEquals(5, Enumerable::range(1, 10)->count(static fn (int $x): bool => $x % 2 === 0));
	}

	public function testDistinct(): void
	{
		static::assertEquals(
			[1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
			Enumerable::range(1, 10)->distinct()->toList(),
		);

		static::assertEquals(
			[1, 3, 2],
			Enumerable::from([1, 1, 3, 2, 3, 1, 2, 3])->distinct()->toList(),
		);
	}

	public function testDistinctBy(): void
	{
		static::assertEquals(
			[1, 2, 3],
			Enumerable::range(1, 10)->distinctBy(static fn (int $x): int => $x % 3)->toList(),
		);

		static::assertEquals(
			[1, 3, 2],
			Enumerable::from([1, 1, 3, 2, 3, 1, 2, 3])->distinctBy(static fn (int $x): int => $x % 3)->toList(),
		);
	}

	public function testExcept(): void
	{
		static::assertEquals(
			[1, 2, 7, 8, 9, 10],
			Enumerable::range(1, 10)->except(Enumerable::range(3, 6))->toList(),
		);
	}

	public function testExceptBy(): void
	{
		static::assertEquals(
			[
				['name' => 'alice', 'age' => 5],
				['name' => 'charlie', 'age' => 4],
			],
			Enumerable::from([
				['name' => 'alice', 'age' => 5],
				['name' => 'bob', 'age' => 10],
				['name' => 'charlie', 'age' => 4],
			])
				->exceptBy(
					Enumerable::from([
						['age' => 10],
					]),
					static fn (array $x): int => $x['age'],
				)
				->toList(),
		);
	}

	public function testFirst(): void
	{
		static::assertEquals(1, Enumerable::range(1, 10)->first());
		static::assertEquals(2, Enumerable::range(1, 10)->first(static fn (int $x): bool => $x % 2 === 0));

		$this->expectException(EmptySequenceException::class);
		Enumerable::empty()->first();
	}

	public function testFirstOrDefault(): void
	{
		static::assertEquals(1, Enumerable::range(1, 10)->firstOrDefault(null));
		static::assertEquals(2, Enumerable::range(1, 10)->firstOrDefault(null, static fn (int $x): bool => $x % 2 === 0));
		static::assertNull(Enumerable::empty()->firstOrDefault(null));
	}

	public function testGroupBy(): void
	{
		static::assertEquals(
			[
				0 => [
					['name' => 'alice', 'age' => 5],
					['name' => 'bob', 'age' => 10],
					['name' => 'dory', 'age' => 5],
				],
				4 => [
					['name' => 'charlie', 'age' => 4],
				],
			],
			Enumerable::from([
				['name' => 'alice', 'age' => 5],
				['name' => 'bob', 'age' => 10],
				['name' => 'charlie', 'age' => 4],
				['name' => 'dory', 'age' => 5],
			])
				->groupBy(static fn (array $x): int => $x['age'] % 5)
				->select(static fn (Contract\Grouping $group): array => $group->toList())
				->toArray(),
		);
	}

	public function testIntersect(): void
	{
		static::assertEquals(
			[3, 4, 5],
			Enumerable::range(1, 5)->intersect(Enumerable::range(3, 8))->toList(),
		);
	}

	public function testIntersectBy(): void
	{
		static::assertEquals(
			[
				['name' => 'bob', 'age' => 10],
			],
			Enumerable::from([
				['name' => 'alice', 'age' => 5],
				['name' => 'bob', 'age' => 10],
				['name' => 'charlie', 'age' => 4],
			])
				->intersectBy(
					Enumerable::from([
						['age' => 10],
					]),
					static fn (array $x): int => $x['age'],
				)
				->toList(),
		);
	}

	public function testIsEmpty(): void
	{
		static::assertTrue(Enumerable::empty()->isEmpty());
	}

	public function testJoin(): void
	{
		static::assertEquals(
			[2, 4, 6, 8, 10],
			Enumerable::range(1, 5)->join(
				Enumerable::range(1, 5),
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
			Enumerable::from(['a', 'b', 'c'])->last(),
		);
	}

	public function testLastThrowsIfEmpty(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		Enumerable::empty()->last();
	}

	public function testLastOrDefault(): void
	{
		static::assertEquals(3, Enumerable::from([1, 2, 3])->lastOrDefault(null));
		static::assertNull(Enumerable::empty()->lastOrDefault(null));
	}

	public function testMax(): void
	{
		static::assertEquals(
			10,
			Enumerable::range(1, 10)->max(static fn (int $x) => $x),
		);
	}

	public function testMaxThrowsIfEmpty(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		Enumerable::empty()->max(static fn (int $x) => $x);
	}

	public function testMin(): void
	{
		static::assertEquals(
			1,
			Enumerable::range(1, 3)->min(static fn (int $x) => $x),
		);
	}

	public function testMinThrowsIfEmpty(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		Enumerable::empty()->min(static fn (int $x) => $x);
	}

	public function testOrderBy(): void
	{
		static::assertEquals(
			[1, 2, 3, 4, 5, 6],
			Enumerable::from([6, 2, 5, 1, 4, 3])->orderBy(static fn (int $x) => $x)->toList(),
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
			Enumerable::from([
				['name' => 'a', 'age' => 1],
				['name' => 'b', 'age' => 2],
			])->orderByDescending(static fn ($x) => $x['age'])->toList(),
		);
	}

	public function testPrepend(): void
	{
		static::assertEquals(
			[4, 5, 1, 2, 3],
			Enumerable::range(1, 3)->prepend(5)->prepend(4)->toList(),
		);
	}

	public function testReverse(): void
	{
		static::assertEquals(
			[5, 4, 3, 2, 1],
			Enumerable::range(1, 5)->reverse()->toArray(),
		);
	}

	public function testSelect(): void
	{
		static::assertEquals(
			[2, 4, 6, 8, 10],
			Enumerable::range(1, 5)
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
			Enumerable::range(1, 5)
				->selectManyKeyed(static fn (int $x): GenericEnumerable => Enumerable::range(1, $x))
				->toList(),
		);
	}

	public function testSequenceEqual(): void
	{
		static::assertTrue(
			Enumerable::range(1, 5)->sequenceEqual(Enumerable::range(1, 5)),
		);

		static::assertFalse(
			Enumerable::range(1, 5)->sequenceEqual(Enumerable::range(1, 6)),
		);

		static::assertTrue(Enumerable::empty()->sequenceEqual(Enumerable::empty()));
	}

	public function testSingle(): void
	{
		static::assertEquals(
			2,
			Enumerable::from([2])->single(),
		);
	}

	public function testSingleMultipleElements(): void
	{
		$this->expectException(AmbiguousMatchException::class);
		$this->expectExceptionMessage('Sequence contains more than one matching element');

		Enumerable::from([1, 2])->single();
	}

	public function testSingleNoElements(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		Enumerable::empty()->single();
	}

	public function testSingleOrDefault(): void
	{
		static::assertEquals(
			1,
			Enumerable::range(1, 5)->singleOrDefault(null, static fn (int $x): bool => $x === 1),
		);

		static::assertNull(
			Enumerable::range(1, 5)->singleOrDefault(null, static fn (int $x): bool => $x === 6),
		);
	}

	public function testSingleOrDefaultMultipleElements(): void
	{
		$this->expectException(AmbiguousMatchException::class);
		$this->expectExceptionMessage('Sequence contains more than one matching element');

		Enumerable::from([1, 2])->singleOrDefault(null);
	}

	public function testSkip(): void
	{
		static::assertEquals(
			[3, 4, 5],
			Enumerable::range(1, 5)
				->skip(2)
				->toList(),
		);
	}

	public function testSkipLast(): void
	{
		static::assertEquals(
			[1, 2, 3],
			Enumerable::range(1, 5)
				->skipLast(2)
				->toList(),
		);

		static::assertEquals(
			[1],
			Enumerable::range(1, 2)
				->skipLast(1)
				->toList(),
		);

		static::assertEquals(
			[],
			Enumerable::range(1, 2)
				->skipLast(2)
				->toList(),
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Count must be greater than zero');

		Enumerable::range(1, 2)->skipLast(0);
	}

	public function testSkipLastEmpty(): void
	{
		$enum = Enumerable::empty()->skipLast(2);

		static::assertInstanceOf(EmptyIterator::class, $enum->getIterator());
	}

	public function testSkipWhile(): void
	{
		static::assertEquals(
			[3, 4, 5],
			Enumerable::range(1, 5)
				->skipWhile(static fn (int $x): bool => $x < 3)
				->toList(),
		);
	}

	public function testSum(): void
	{
		static::assertEquals(15, Enumerable::range(1, 5)->sum(static fn ($x) => $x));
	}

	public function testTake(): void
	{
		static::assertEquals(
			[0, 1, 2],
			Enumerable::range(0, 6)->take(3)->toList(),
		);
	}

	public function testTakeLast(): void
	{
		static::assertEquals(
			[5, 6],
			Enumerable::range(0, 6)->takeLast(2)->toList(),
		);
	}

	public function testTakeLastInvalid(): void
	{
		static::assertEquals(
			[],
			Enumerable::range(0, 6)->takeLast(-2)->toList(),
		);
	}

	public function testTakeLastEmpty(): void
	{
		static::assertEquals(
			[],
			Enumerable::empty()->takeLast(1)->toList(),
		);
	}

	public function testTakeWhile(): void
	{
		static::assertEquals(
			[0, 1, 2],
			Enumerable::range(0, 6)->takeWhile(static fn (int $x): bool => $x < 3)->toList(),
		);
	}

	public function testToNestedArray(): void
	{
		static::assertEquals(
			[
				2 => ['a', 'c', 'e'],
				3 => ['b', 'd'],
			],
			Enumerable::from([
				'a',
				'b',
				'c',
				'd',
				'e',
			])->toNestedArray(static fn (int $k): int => $k % 2 + 2),
		);
	}

	public function testToKeyed(): void
	{
		static::assertEquals(
			['a' => 97, 'b' => 98, 'c' => 99],
			Enumerable::range(97, 99)->toKeyed(static fn ($x) => chr($x))->toArray(),
		);
	}

	public function testUnion(): void
	{
		$a = Enumerable::from([5, 3, 9, 7, 5, 9, 3, 7]);
		$b = Enumerable::from([8, 3, 6, 4, 4, 9, 1, 0]);

		static::assertEquals(
			[5, 3, 9, 7, 8, 6, 4, 1, 0],
			$a->union($b)->toList(),
		);
	}

	public function testUnionBy(): void
	{
		$a = Enumerable::from([5, 3, 9, 7, 5, 9, 3, 7]);
		$b = Enumerable::from([8, 3, 6, 4, 4, 9, 1, 0]);

		static::assertEquals(
			[5, 3, 9, 7, 6],
			$a->unionBy($b, static fn (int $a) => $a % 5)->toList(),
		);
	}

	public function testWhere(): void
	{
		static::assertEquals(
			[5, 6, 7],
			Enumerable::range(1, 7)->where(static fn ($x) => $x > 4)->toList(),
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
			Enumerable::range(1, 3)->zip(Enumerable::range(4, 6))->toList(),
		);
	}

	public function testDoubleEnumerationIsPossibleWithGeneratorFunction(): void
	{
		$enumerable = new Enumerable(static function () {
			yield 1;
			yield 2;
		});

		static::assertFalse($enumerable->isEmpty());
		static::assertFalse($enumerable->isEmpty());
	}

	public function testClosureWithSameGeneratorThrows(): void
	{
		$generator = (static function () {
			yield 1;
			yield 2;
		})();

		$enumerable = new Enumerable(static fn () => $generator);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Cannot traverse an already closed generator');

		$enumerable->isEmpty();
		$enumerable->isEmpty();
	}

	public function testDoubleEnumerationWithGeneratorGeneratorClosure(): void
	{
		$enumerable = new Enumerable(static fn () => (static function () {
			yield 1;
			yield 2;
		})());

		static::assertFalse($enumerable->isEmpty());
		static::assertFalse($enumerable->isEmpty());
	}

	public function testGeneratorGetsWrappedInEagerCachingIterator(): void
	{
		$generator = (static function () {
			yield 1;
			yield 2;
		})();

		$enum = new Enumerable($generator);
		static::assertInstanceOf(EagerCachingIterator::class, $enum->getIterator());
	}

	public function testDoubleEnumerationWithGeneratorObjectWithCachedIterator(): void
	{
		$generator = (static function () {
			yield 1;
			yield 2;
		})();

		$iterator = new EagerCachingIterator($generator);

		$enumerable = new Enumerable($iterator);

		static::assertFalse($enumerable->isEmpty());
		static::assertFalse($enumerable->isEmpty());
	}
}
