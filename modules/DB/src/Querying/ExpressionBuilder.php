<?php
declare(strict_types=1);

namespace Elephox\DB\Querying;

use Elephox\DB\Querying\Contract\QueryValue;
use Elephox\DB\Querying\Contract\SelectQueryBuilder;
use Elephox\DB\Querying\Values\BoundListQueryValue;
use Elephox\DB\Querying\Values\BoundQueryValue;
use Elephox\DB\Querying\Values\ColumnReferenceQueryValue;

final class ExpressionBuilder implements Contract\ExpressionBuilder
{
	public function __construct(
		private SelectQueryBuilder $builder,
		private ColumnReferenceQueryValue $columnName,
		private bool $inverted = false,
		private ?string $operator = null,
		private null|Contract\QueryExpression|QueryValue $value = null,
	) {
	}

	public function not(): Contract\ExpressionBuilder
	{
		return new self($this->builder, $this->columnName, !$this->inverted, $this->operator, $this->value);
	}

	public function equals(string $paramName): SelectQueryBuilder
	{
		$this->operator = "=";
		$this->value = new BoundQueryValue($paramName);

		return $this->builder;
	}

	public function greaterThan(string $paramName): SelectQueryBuilder
	{
		$this->operator = ">";
		$this->value = new BoundQueryValue($paramName);

		return $this->builder;
	}

	public function lessThan(string $paramName): SelectQueryBuilder
	{
		$this->operator = "<";
		$this->value = new BoundQueryValue($paramName);

		return $this->builder;
	}

	public function like(string $paramName): SelectQueryBuilder
	{
		$this->operator = "LIKE";
		$this->value = new BoundQueryValue($paramName);

		return $this->builder;
	}

	public function in(string $paramName): SelectQueryBuilder
	{
		$this->operator = "IN";
		$this->value = new BoundListQueryValue($paramName);

		return $this->builder;
	}

	public function build(): Contract\QueryExpression
	{
		return new QueryExpression($this->columnName, $this->operator, $this->value);
	}
}
