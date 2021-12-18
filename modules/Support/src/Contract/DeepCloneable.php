<?php
declare(strict_types=1);

namespace Elephox\Support\Contract;

interface DeepCloneable
{
	public function deepClone(): static;
}
