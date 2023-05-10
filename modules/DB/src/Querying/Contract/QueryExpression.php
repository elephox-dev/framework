<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

use Stringable;

interface QueryExpression extends Stringable
{
	public function getLeft(): QueryValue|QueryExpression;

	public function getOperator(): string;

	public function getRight(): QueryValue|QueryExpression;
}
