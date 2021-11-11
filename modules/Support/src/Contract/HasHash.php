<?php
declare(strict_types=1);

namespace Elephox\Support\Contract;

interface HasHash
{
	public function getHash(): string|int;
}
