<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

interface QueryParameter
{
	public function getName(): string;
	public function getValue(): mixed;
}
