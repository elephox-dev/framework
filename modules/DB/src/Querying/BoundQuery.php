<?php
declare(strict_types=1);

namespace Elephox\DB\Querying;

use Elephox\DB\Abstraction\Contract\QueryAdapter;
use Elephox\DB\Querying\Contract\Query;
use Elephox\DB\Querying\Contract\QueryParameters;
use Elephox\DB\Querying\Contract\QueryResult;

final readonly class BoundQuery implements Contract\BoundQuery
{
	public function __construct(
		private Query $query,
		private QueryParameters $parameters,
	) {
	}

	public function run(QueryAdapter $adapter): QueryResult
	{
		return $adapter->run($this);
	}

	public function getQuery(): Query
	{
		return $this->query;
	}

	public function getParameters(): QueryParameters
	{
		return $this->parameters;
	}
}
