<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

use Stringable;

interface Query extends Stringable
{
	public function getDefinition(): QueryDefinition;

	public function bind(QueryParameters $parameters): BoundQuery;
}
