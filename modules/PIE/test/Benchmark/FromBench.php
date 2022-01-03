<?php
declare(strict_types=1);

namespace Elephox\PIE\Benchmark;

use ArrayIterator;
use Elephox\PIE\Enumerable;
use Elephox\PIE\PIE;
use PhpBench\Attributes\Revs;

class FromBench
{
	#[Revs(100)]
	public function benchFrom()
	{
		$string = PIE::from('foo');
		$array = PIE::from(['foo' => 'bar']);
		$iterator = PIE::from(new ArrayIterator(['foo' => 'bar']));
		$enumerable = PIE::from(new Enumerable(new ArrayIterator(['foo' => 'bar'])));

		$string->concat($array, $iterator, $enumerable)->toArray();
	}
}
