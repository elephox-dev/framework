<?php
declare(strict_types=1);

namespace Elephox\Collection\Benchmark\ArrayList;

use Elephox\Collection\ArrayList;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;

class ConstructorBench
{
	#[Revs(1000)]
	#[Iterations(10)]

	public function benchConstruct(): void
	{
		new ArrayList();
	}

	#[Revs(1000)]
	#[Iterations(10)]
	public function benchConstructWithEmptyList(): void
	{
		new ArrayList([]);
	}

	#[Revs(1000)]
	#[Iterations(10)]
	public function benchConstructWithInitialList(): void
	{
		new ArrayList([123, 456, 789]);
	}
}
