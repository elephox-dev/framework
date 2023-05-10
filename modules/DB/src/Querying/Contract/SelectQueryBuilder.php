<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

interface SelectQueryBuilder extends QueryBuilder
{
	public function from(string $table, ?string $alias = null): self;

	public function where(string $columnName): ExpressionBuilder;

	public function build(): ResultSetQuery;
}
