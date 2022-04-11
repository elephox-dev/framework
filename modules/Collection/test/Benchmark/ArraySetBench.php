<?php
declare(strict_types=1);

namespace Elephox\Collection\Benchmark;

use Elephox\Collection\ArraySet;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;

class ArraySetBench
{
	/** @var ArraySet<int> */
	private ArraySet $set;

	public function __construct()
	{
		$this->set = new ArraySet(range(1, 1000));
	}

	#[Revs(100)]
	#[Iterations(10)]
	public function benchContains(): void
	{
		$this->set->contains(50);
	}

	#[Revs(100)]
	#[Iterations(10)]
	public function benchFirstOrDefault(): void
	{
		$this->set->firstOrDefault(0);
		$this->set->firstOrDefault(0, fn (int $v) => $v % 2 === 0);
	}
}
