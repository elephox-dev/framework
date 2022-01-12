<?php
declare(strict_types=1);

namespace Elephox\Collection;

use PHPUnit\Framework\TestCase;

class OrderedEnumerableTest extends TestCase
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
			->orderBy(fn($x) => $x['age'])
			->thenBy(fn($x) => strlen($x['name']))
			->toList();

		self::assertEquals(
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
			$ordered
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
		->orderByDescending(fn ($x) => $x['age'])
		->thenByDescending(fn ($x) => strlen($x['name']))
		->toList();

		self::assertEquals(
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
			$ordered
		);
	}
}
