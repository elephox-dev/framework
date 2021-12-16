<?php
declare(strict_types=1);

namespace Elephox\Collection\Contract;

interface DeepCloneable
{
	public function deepClone(): self;
}
