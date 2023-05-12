<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

interface ExpressionBuilder
{
	public function not(): self;
	public function equals(string $paramName): SelectQueryBuilder;
	public function greaterThan(string $paramName): SelectQueryBuilder;
	public function lessThan(string $paramName): SelectQueryBuilder;
	public function like(string $paramName): SelectQueryBuilder;
	public function in(string $paramName): SelectQueryBuilder;
	public function build(): QueryExpression;
}
