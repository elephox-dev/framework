<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

interface InsertQueryBuilder extends QueryBuilder
{
	public function values(iterable $values): self;

	public function value(string $paramName, mixed $value): self;

	public function build(): ExecutableQuery;
}
