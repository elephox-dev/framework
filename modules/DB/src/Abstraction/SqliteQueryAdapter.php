<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\DB\Abstraction\Contract\QueryAdapter;
use Elephox\DB\Querying\Contract\BoundQuery;
use Elephox\DB\Querying\Contract\QueryResult;

class SqliteQueryAdapter implements QueryAdapter
{
	public function run(BoundQuery $query): QueryResult
	{
		// TODO: Implement run() method.
	}
}
