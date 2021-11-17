<?php
declare(strict_types=1);

namespace Elephox\Support\Contract;

use Stringable;

interface StringConvertible extends Stringable
{
	public function toString(): string;
}
