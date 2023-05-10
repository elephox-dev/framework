<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

use Elephox\DB\Abstraction\Contract\QueryAdapter;

interface BoundQuery
{
	public function run(QueryAdapter $adapter): QueryResult;

	public function getQuery(): Query;

	public function getParameters(): QueryParameters;
}
