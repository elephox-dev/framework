<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

interface Comparable
{
	#[Pure]
	#[ExpectedValues([-1, 0, 1])]
	public function compareTo(object $other): int;
}
