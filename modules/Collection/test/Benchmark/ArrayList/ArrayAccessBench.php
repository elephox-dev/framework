<?php
declare(strict_types=1);

namespace Elephox\Collection\Benchmark\ArrayList;

use Elephox\Collection\ArrayList;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Revs;

#[BeforeMethods('setUp')]
class ArrayAccessBench
{
	private ArrayList $arrayList;

	public function setUp(): void
	{
		$this->arrayList = new ArrayList(range(1, 1000));
	}

	#[Revs(100)]
	public function benchOffsetExists(): void
	{
		$this->arrayList->offsetExists(50);
	}

	#[Revs(100)]
	public function benchOffsetGet(): void
	{
		$this->arrayList->offsetGet(10);
	}

	#[Revs(100)]
	public function benchOffsetSet(): void
	{
		$this->arrayList->offsetSet(0, 1);
	}

	#[Revs(100)]
	public function benchOffsetUnset(): void
	{
		static $i;

		$i ??= 1000;

		$this->arrayList->offsetUnset($i--);
	}
}
