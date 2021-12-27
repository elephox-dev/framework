<?php
declare(strict_types=1);

namespace Elephox\PIE;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;

interface Comparable
{
	/**
	 * @param object $other
	 * @return int<-1, 1>
	 */
	#[Pure]
	#[ExpectedValues([-1, 0, 1])]
	public function compareTo(object $other): int;
}
