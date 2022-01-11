<?php
declare(strict_types=1);

namespace Elephox\Collection\Benchmark;

use ArrayIterator;
use Elephox\Collection\KeyedEnumerable;
use PhpBench\Attributes\Revs;

class FromBench
{
	#[Revs(100)]
	public function benchFrom(): void
	{
		$string = KeyedEnumerable::from('foo');
		$array = KeyedEnumerable::from(['foo' => 'bar']);
		$iterator = KeyedEnumerable::from(new ArrayIterator(['foo' => 'bar']));
		$enumerable = KeyedEnumerable::from(new KeyedEnumerable(new ArrayIterator(['foo' => 'bar'])));

		$string->concat($array, $iterator, $enumerable)->toArray();
	}
}
