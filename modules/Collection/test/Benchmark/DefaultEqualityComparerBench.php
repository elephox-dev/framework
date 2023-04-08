<?php
declare(strict_types=1);

namespace Elephox\Collection\Benchmark;

use Elephox\Collection\DefaultEqualityComparer;
use Elephox\Collection\DefaultEqualityComparerTest;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;

class DefaultEqualityComparerBench {
	#[Revs(100)]
	#[Iterations(10)]
	public function benchEquals(): void
	{
		foreach (DefaultEqualityComparerTest::equalsDataProvider() as [$a, $b, ]) {
			DefaultEqualityComparer::equals($a, $b);
		}
	}

	#[Revs(100)]
	#[Iterations(10)]
	public function benchCompare(): void
	{
		foreach (DefaultEqualityComparerTest::compareDataProvider() as [$a, $b, ]) {
			DefaultEqualityComparer::equals($a, $b);
		}
	}

	#[Revs(100)]
	#[Iterations(10)]
	public function benchSame(): void
	{
		foreach (DefaultEqualityComparerTest::sameDataProvider() as [$a, $b, ]) {
			DefaultEqualityComparer::same($a, $b);
		}
	}
}
