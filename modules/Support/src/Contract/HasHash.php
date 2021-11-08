<?php
declare(strict_types=1);

namespace Philly\Support\Contract;

interface HasHash
{
	public function getHash(): string|int;
}
