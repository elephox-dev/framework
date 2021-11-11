<?php
declare(strict_types=1);

namespace Elephox\Support\Contract;

interface HashGenerator
{
	public function generateHash(object $object): string|int;
}
