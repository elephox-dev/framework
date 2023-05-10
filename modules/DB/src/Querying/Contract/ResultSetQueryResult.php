<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

use Elephox\Collection\Contract\GenericEnumerable;

interface ResultSetQueryResult extends QueryResult
{
	public function getResults(): GenericEnumerable;
}
