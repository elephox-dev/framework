<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction\Contract;

use Elephox\DB\Querying\Contract\BoundQuery;
use Elephox\DB\Querying\Contract\QueryResult;

interface QueryAdapter
{
	public function run(BoundQuery $query): QueryResult;
}
