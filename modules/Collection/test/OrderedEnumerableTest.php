<?php
declare(strict_types=1);

namespace Elephox\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\Enumerable
 * @covers \Elephox\Collection\OrderedEnumerable
 * @covers \Elephox\Collection\Iterator\OrderedIterator
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\KeyedEnumerable
 * @covers \Elephox\Collection\IteratorProvider
 *
 * @uses \Elephox\Collection\IsKeyedEnumerable
 *
 * @internal
 */
final class OrderedEnumerableTest extends TestCase
{
	public function testThenBy(): void
	{
		$ordered = Enumerable::from([
			[
				'age' => 20,
				'name' => 'John',
			],
			[
				'age' => 10,
				'name' => 'Thompson',
			],
			[
				'age' => 30,
				'name' => 'Jane',
			],
			[
				'age' => 10,
				'name' => 'John',
			],
		])
			->orderBy(static fn ($x) => $x['age'])
			->thenBy(static fn ($x) => strlen($x['name']))
			->toList()
		;

		self::assertSame(
			[
				[
					'age' => 10,
					'name' => 'John',
				],
				[
					'age' => 10,
					'name' => 'Thompson',
				],
				[
					'age' => 20,
					'name' => 'John',
				],
				[
					'age' => 30,
					'name' => 'Jane',
				],
			],
			$ordered,
		);
	}

	public function testThenByDescending(): void
	{
		$ordered = Enumerable::from([
			[
				'age' => 20,
				'name' => 'John',
			],
			[
				'age' => 10,
				'name' => 'Thompson',
			],
			[
				'age' => 30,
				'name' => 'Jane',
			],
			[
				'age' => 10,
				'name' => 'John',
			],
		])
			->orderByDescending(static fn ($x) => $x['age'])
			->thenByDescending(static fn ($x) => strlen($x['name']))
			->toList()
		;

		self::assertSame(
			[
				[
					'age' => 30,
					'name' => 'Jane',
				],
				[
					'age' => 20,
					'name' => 'John',
				],
				[
					'age' => 10,
					'name' => 'Thompson',
				],
				[
					'age' => 10,
					'name' => 'John',
				],
			],
			$ordered,
		);
	}
}
