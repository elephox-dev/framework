<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

interface QueryStarter
{
	public function select(string|iterable $columns): SelectQueryBuilder;

	public function insert(string $into): InsertQueryBuilder;
}
