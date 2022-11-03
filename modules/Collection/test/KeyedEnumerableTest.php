<?php
declare(strict_types=1);

namespace Elephox\Collection;

use ArrayIterator;
use AssertionError;
use Elephox\Collection\Contract\GenericKeyedEnumerable;
use Elephox\Collection\Iterator\EagerCachingIterator;
use EmptyIterator;
use Exception;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use stdClass;
use Stringable;

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
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
class KeyedEnumerableTest extends TestCase
{
	public function testFromString(): void
	{
		static::assertSame(
			['a'],
			KeyedEnumerable::from('a')->toList(),
		);
	}

	public function testFromIterator(): void
	{
		static::assertSame(
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
		$this->expectExceptionMessage('Value must be iterable');

		KeyedEnumerable::from(null);
	}

	public function testGetIteratorThrowsForInvalidClosure(): void
	{
		$this->expectException(AssertionError::class);
		$this->expectExceptionMessage('Given iterator generator does not return an iterator');

		$enum = new KeyedEnumerable(static fn () => null);
		$enum->getIterator();
	}

	public function testAggregate(): void
	{
		static::assertSame(
			120,
			KeyedEnumerable::range(1, 5)->aggregate(static fn ($a, $b) => $a * $b, 1),
		);

		static::assertSame(
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
		static::assertSame(
			[1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd', 5 => 'e'],
			KeyedEnumerable::from([1 => 'a', 2 => 'b', 3 => 'c'])->append(4, 'd')->append(5, 'e')->toArray(),
		);
	}

	public function testAppendAll(): void
	{
		static::assertSame(
			[1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd', 5 => 'e'],
			KeyedEnumerable::from([1 => 'a', 2 => 'b', 3 => 'c'])->appendAll([4 => 'd', 5 => 'e'])->toArray(),
		);
	}

	public function testAverage(): void
	{
		static::assertSame(2, KeyedEnumerable::range(1, 3)->average(static fn (int $x) => $x));
	}

	public function testAverageThrowsIfEmpty(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		KeyedEnumerable::empty()->average(static fn (int $x) => $x);
	}

	public function testChunk(): void
	{
		static::assertSame(
			[
				[1, 2, 3],
				[4, 5, 6],
				[7, 8, 9],
			],
			KeyedEnumerable::range(1, 9)->chunk(3)->toList(),
		);
	}

	public function testChunkInvalidSize(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Chunk size must be greater than zero');

		KeyedEnumerable::range(1, 3)->chunk(0);
	}

	public function testConcat(): void
	{
		static::assertSame(
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
		static::assertSame(10, KeyedEnumerable::range(1, 10)->count());
		static::assertSame(5, KeyedEnumerable::range(1, 10)->count(static fn (int $x): bool => $x % 2 === 0));
	}

	public function testDistinct(): void
	{
		static::assertSame(
			[1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
			KeyedEnumerable::range(1, 10)->distinct()->toList(),
		);

		static::assertSame(
			[1, 3, 2],
			KeyedEnumerable::from([1, 1, 3, 2, 3, 1, 2, 3])->distinct()->toList(),
		);
	}

	public function testDistinctBy(): void
	{
		static::assertSame(
			[1, 2, 3],
			KeyedEnumerable::range(1, 10)->distinctBy(static fn (int $x): int => $x % 3)->toList(),
		);

		static::assertSame(
			[1, 3, 2],
			KeyedEnumerable::from([1, 1, 3, 2, 3, 1, 2, 3])->distinctBy(static fn (int $x): int => $x % 3)->toList(),
		);
	}

	public function testExcept(): void
	{
		static::assertSame(
			[1, 2, 7, 8, 9, 10],
			KeyedEnumerable::range(1, 10)->except(KeyedEnumerable::range(3, 6))->toList(),
		);
	}

	public function testExceptBy(): void
	{
		static::assertSame(
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
		static::assertSame(1, KeyedEnumerable::range(1, 10)->first());
		static::assertSame(2, KeyedEnumerable::range(1, 10)->first(static fn (int $x): bool => $x % 2 === 0));

		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');
		KeyedEnumerable::empty()->first();
	}

	public function testFirstKey(): void
	{
		static::assertSame(0, KeyedEnumerable::range(1, 10)->firstKey());
		static::assertSame(1, KeyedEnumerable::range(1, 10)->firstKey(static fn (int $x): bool => $x % 2 === 0));

		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');
		KeyedEnumerable::empty()->firstKey();
	}

	public function testFirstOrDefault(): void
	{
		static::assertSame(1, KeyedEnumerable::range(1, 10)->firstOrDefault(null));
		static::assertSame(2, KeyedEnumerable::range(1, 10)->firstOrDefault(null, static fn (int $x): bool => $x % 2 === 0));
		static::assertNull(KeyedEnumerable::empty()->firstOrDefault(null));
	}

	public function testFirstKeyOrDefault(): void
	{
		static::assertSame(0, KeyedEnumerable::range(1, 10)->firstKeyOrDefault(null));
		static::assertSame(1, KeyedEnumerable::range(1, 10)->firstKeyOrDefault(null, static fn (int $x): bool => $x % 2 === 0));
		static::assertNull(KeyedEnumerable::empty()->firstKeyOrDefault(null));
	}

	public function testGroupBy(): void
	{
		static::assertSame(
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
			KeyedEnumerable::from([
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

	public function testFlip(): void
	{
		static::assertSame(
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
		static::assertSame(
			[3, 4, 5],
			KeyedEnumerable::range(1, 5)->intersect(KeyedEnumerable::range(3, 8))->toList(),
		);
	}

	public function testIntersectBy(): void
	{
		static::assertSame(
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
		static::assertSame(
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
		static::assertSame(
			'c',
			KeyedEnumerable::from(['a', 'b', 'c'])->last(),
		);
	}

	public function testLastThrowsIfEmpty(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		KeyedEnumerable::empty()->last();
	}

	public function testLastOrDefault(): void
	{
		static::assertSame(3, KeyedEnumerable::from([1, 2, 3])->lastOrDefault(null));
		static::assertNull(KeyedEnumerable::empty()->lastOrDefault(null));
	}

	public function testMax(): void
	{
		static::assertSame(
			10,
			KeyedEnumerable::range(1, 10)->max(static fn (int $x) => $x),
		);
	}

	public function testMaxThrowsIfEmpty(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		KeyedEnumerable::empty()->max(static fn (int $x) => $x);
	}

	public function testMin(): void
	{
		static::assertSame(
			1,
			KeyedEnumerable::range(1, 3)->min(static fn (int $x) => $x),
		);
	}

	public function testMinThrowsIfEmpty(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		KeyedEnumerable::empty()->min(static fn (int $x) => $x);
	}

	public function testOrderBy(): void
	{
		static::assertSame(
			[1, 2, 3, 4, 5, 6],
			KeyedEnumerable::from([6, 2, 5, 1, 4, 3])->orderBy(static fn (int $x) => $x)->toList(),
		);
	}

	public function testOrderByDescending(): void
	{
		static::assertSame(
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
		static::assertSame(
			['e' => 5, 'd' => 4, 'a' => 1, 'b' => 2, 'c' => 3],
			KeyedEnumerable::from(['a' => 1, 'b' => 2, 'c' => 3])->prepend('d', 4)->prepend('e', 5)->toArray(),
		);
	}

	public function testReverse(): void
	{
		static::assertSame(
			[5, 4, 3, 2, 1],
			KeyedEnumerable::range(1, 5)->reverse()->toArray(),
		);
	}

	public function testSelect(): void
	{
		static::assertSame(
			[2, 4, 6, 8, 10],
			KeyedEnumerable::range(1, 5)
				->select(static fn (int $x): int => $x * 2)
				->toList(),
		);
	}

	public function testSelectKeys(): void
	{
		static::assertSame(
			[0 => 1, 2 => 2, 4 => 3, 6 => 4, 8 => 5],
			KeyedEnumerable::range(1, 5)
				->selectKeys(static fn (int $k): int => $k * 2)
				->toArray(),
		);
	}

	public function testSelectMany(): void
	{
		static::assertSame(
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
		static::assertSame(
			2,
			KeyedEnumerable::from([2])->single(),
		);
	}

	public function testSingleMultipleElements(): void
	{
		$this->expectException(AmbiguousMatchException::class);
		$this->expectExceptionMessage('Sequence contains more than one matching element');

		KeyedEnumerable::from([1, 2])->single();
	}

	public function testSingleNoElements(): void
	{
		$this->expectException(EmptySequenceException::class);
		$this->expectExceptionMessage('The sequence contains no elements');

		KeyedEnumerable::empty()->single();
	}

	public function testSingleOrDefault(): void
	{
		static::assertSame(
			1,
			KeyedEnumerable::range(1, 5)->singleOrDefault(null, static fn (int $x): bool => $x === 1),
		);

		static::assertNull(
			KeyedEnumerable::range(1, 5)->singleOrDefault(null, static fn (int $x): bool => $x === 6),
		);
	}

	public function testSingleOrDefaultMultipleElements(): void
	{
		$this->expectException(AmbiguousMatchException::class);
		$this->expectExceptionMessage('Sequence contains more than one matching element');

		KeyedEnumerable::from([1, 2])->singleOrDefault(null);
	}

	public function testSkip(): void
	{
		static::assertSame(
			[3, 4, 5],
			KeyedEnumerable::range(1, 5)
				->skip(2)
				->toList(),
		);
	}

	public function testSkipLast(): void
	{
		static::assertSame(
			[1, 2, 3],
			KeyedEnumerable::range(1, 5)
				->skipLast(2)
				->toList(),
		);

		static::assertSame(
			[1],
			KeyedEnumerable::range(1, 2)
				->skipLast(1)
				->toList(),
		);

		static::assertSame(
			[],
			KeyedEnumerable::range(1, 2)
				->skipLast(2)
				->toList(),
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Count must be greater than zero');

		KeyedEnumerable::range(1, 2)->skipLast(0);
	}

	public function testSkipLastEmpty(): void
	{
		$enum = KeyedEnumerable::empty()->skipLast(2);

		static::assertInstanceOf(EmptyIterator::class, $enum->getIterator());
	}

	public function testSkipWhile(): void
	{
		static::assertSame(
			[3, 4, 5],
			KeyedEnumerable::range(1, 5)
				->skipWhile(static fn (int $x): bool => $x < 3)
				->toList(),
		);
	}

	public function testTake(): void
	{
		static::assertSame(
			[0, 1, 2],
			KeyedEnumerable::range(0, 6)->take(3)->toList(),
		);
	}

	public function testTakeLast(): void
	{
		static::assertSame(
			[5, 6],
			KeyedEnumerable::range(0, 6)->takeLast(2)->toList(),
		);
	}

	public function testTakeLastInvalid(): void
	{
		static::assertSame(
			[],
			KeyedEnumerable::range(0, 6)->takeLast(-2)->toList(),
		);
	}

	public function testTakeLastEmpty(): void
	{
		static::assertSame(
			[],
			KeyedEnumerable::empty()->takeLast(1)->toList(),
		);
	}

	public function testTakeWhile(): void
	{
		static::assertSame(
			[0, 1, 2],
			KeyedEnumerable::range(0, 6)->takeWhile(static fn (int $x): bool => $x < 3)->toList(),
		);
	}

	public function testToArray(): void
	{
		static::assertSame(
			[1, 2, 3],
			KeyedEnumerable::from([1, 2, 3])->toArray(),
		);

		static::assertSame(
			['stringable' => 1],
			(new KeyedEnumerable(static fn () => yield new class implements Stringable {
				public function __toString(): string
				{
					return 'stringable';
				}
			} => 1))->toArray(),
		);

		$this->expectException(OutOfBoundsException::class);
		$this->expectExceptionMessage('Invalid array key: stdClass');
		(new KeyedEnumerable(static fn () => yield new stdClass() => 1))->toArray();
	}

	public function testToNestedArray(): void
	{
		static::assertSame(
			[
				2 => ['a', 'c', 'e'],
				3 => ['b', 'd'],
			],
			KeyedEnumerable::from([
				'a',
				'b',
				'c',
				'd',
				'e',
			])->toNestedArray(static fn (int $k): int => $k % 2 + 2),
		);

		static::assertSame(
			[
				'stringable' => ['a'],
			],
			(new KeyedEnumerable(static fn () => yield new class implements Stringable {
				public function __toString(): string
				{
					return 'stringable';
				}
			} => 'a'))->toNestedArray(),
		);

		$this->expectException(OutOfBoundsException::class);
		$this->expectExceptionMessage('Invalid array key: stdClass');

		(new KeyedEnumerable(static fn () => yield new stdClass() => 'a'))->toNestedArray();
	}

	public function testUnion(): void
	{
		$a = KeyedEnumerable::from([5, 3, 9, 7, 5, 9, 3, 7]);
		$b = KeyedEnumerable::from([8, 3, 6, 4, 4, 9, 1, 0]);

		static::assertSame(
			[5, 3, 9, 7, 8, 6, 4, 1, 0],
			$a->union($b)->toList(),
		);
	}

	public function testUnionBy(): void
	{
		$a = KeyedEnumerable::from([5, 3, 9, 7, 5, 9, 3, 7]);
		$b = KeyedEnumerable::from([8, 3, 6, 4, 4, 9, 1, 0]);

		static::assertSame(
			[5, 3, 9, 7, 6],
			$a->unionBy($b, static fn (int $a) => $a % 5)->toList(),
		);
	}

	public function testWhereKey(): void
	{
		static::assertSame(
			['b' => 2, 'c' => 3],
			KeyedEnumerable::from(['a' => 1, 'b' => 2, 'c' => 3])->whereKey(static fn ($x) => $x > 'a')->toArray(),
		);
	}

	public function testZip(): void
	{
		static::assertSame(
			[
				[1, 4],
				[2, 5],
				[3, 6],
			],
			KeyedEnumerable::range(1, 3)->zip(KeyedEnumerable::range(4, 6))->toList(),
		);
	}

	public function testDoubleEnumerationIsPossibleWithGeneratorFunction(): void
	{
		$enumerable = new KeyedEnumerable(static function () {
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

		$enumerable = new KeyedEnumerable(static fn () => $generator);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Cannot traverse an already closed generator');

		$enumerable->isEmpty();
		$enumerable->isEmpty();
	}

	public function testDoubleEnumerationWithGeneratorGeneratorClosure(): void
	{
		$enumerable = new KeyedEnumerable(static fn () => (static function () {
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

		$enum = new KeyedEnumerable($generator);
		static::assertInstanceOf(EagerCachingIterator::class, $enum->getIterator());
	}

	public function testDoubleEnumerationWithGeneratorObjectWithCachedIterator(): void
	{
		$generator = (static function () {
			yield 1;
			yield 2;
		})();

		$iterator = new EagerCachingIterator($generator);

		$enumerable = new KeyedEnumerable($iterator);

		static::assertFalse($enumerable->isEmpty());
		static::assertFalse($enumerable->isEmpty());
	}
}
