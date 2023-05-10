<?php
declare(strict_types=1);

namespace Elephox\DB\Querying;

use Elephox\DB\Querying\Contract\QueryExpression as QueryExpressionContract;
use Elephox\DB\Querying\Contract\QueryValue as QueryValueContract;

final readonly class QueryExpression implements QueryExpressionContract
{
	public function __construct(
		private QueryValueContract|QueryExpressionContract $left,
		private string $operator,
		private QueryValueContract|QueryExpressionContract $right,
	) {
	}

	public function getLeft(): QueryValueContract|QueryExpressionContract
	{
		return $this->left;
	}

	public function getOperator(): string
	{
		return $this->operator;
	}

	public function getRight(): QueryValueContract|QueryExpressionContract
	{
		return $this->right;
	}

	public function __toString(): string
	{
		return $this->getLeft() . ' ' . $this->getOperator() . ' ' . $this->getRight();
	}
}
