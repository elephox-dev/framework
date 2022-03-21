<?php

namespace Elephox\Collection;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Elephox\Collection\GroupedEnumerable
 * @covers \Elephox\Collection\Enumerable
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

		$enum->groupBy(fn($item): int => $item['age']);

		self::assertEquals(
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
			$enum->toArray()
		);
	}
}
