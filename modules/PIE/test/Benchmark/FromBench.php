<?php
declare(strict_types=1);

namespace Elephox\PIE\Benchmark;

use ArrayIterator;
use Elephox\PIE\Enumerable;
use PhpBench\Attributes\Revs;

class FromBench
{
	#[Revs(100)]
	public function benchFrom(): void
	{
		$string = Enumerable::from('foo');
		$array = Enumerable::from(['foo' => 'bar']);
		$iterator = Enumerable::from(new ArrayIterator(['foo' => 'bar']));
		$enumerable = Enumerable::from(new Enumerable(new ArrayIterator(['foo' => 'bar'])));

		$string->concat($array, $iterator, $enumerable)->toArray();
	}
}
