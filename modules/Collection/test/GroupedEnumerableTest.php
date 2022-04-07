<?php
declare(strict_types=1);

namespace Elephox\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\GroupedEnumerable
 * @covers \Elephox\Collection\Enumerable
 * @covers \Elephox\Collection\DefaultEqualityComparer
 * @covers \Elephox\Collection\Grouping
 * @covers \Elephox\Collection\IsKeyedEnumerable
 * @covers \Elephox\Collection\Iterator\GroupingIterator
 * @covers \Elephox\Collection\KeyedEnumerable
 *
 * @internal
 */
class GroupedEnumerableTest extends TestCase
{
	public function testBasicGrouping(): void
	{
		$enum = GroupedEnumerable::from([
			['name' => 'John', 'age' => 20],
			['name' => 'Jane', 'age' => 20],
			['name' => 'Joe', 'age' => 30],
			['name' => 'Jack', 'age' => 30],
			['name' => 'Jones', 'age' => 40],
		]);

		static::assertEquals(
			[
				20 => [
					['name' => 'John', 'age' => 20],
					['name' => 'Jane', 'age' => 20],
				],
				30 => [
					['name' => 'Joe', 'age' => 30],
					['name' => 'Jack', 'age' => 30],
				],
				40 => [
					['name' => 'Jones', 'age' => 40],
				],
			],
			$enum
				->groupBy(static fn ($item) => $item['age'])
				->selectManyKeyed(static fn ($group) => $group, keySelector: static fn ($group) => $group->groupKey())
				->toNestedArray(),
		);
	}
}
