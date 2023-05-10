<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

interface QueryBuilder
{
	public function build(): Query;
}
