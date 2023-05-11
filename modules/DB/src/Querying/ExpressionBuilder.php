<?php
declare(strict_types=1);

namespace Elephox\DB\Querying;

use Elephox\DB\Querying\Contract\SelectQueryBuilder;

final readonly class ExpressionBuilder implements Contract\ExpressionBuilder
{
	public function __construct(
		private SelectQueryBuilder $builder,
		private string $columnName,
		private bool $inverted = false,
	) {
	}

	public function not(): Contract\ExpressionBuilder
	{
		return new self($this->builder, $this->columnName, !$this->inverted);
	}

	public function equals(string $paramName): SelectQueryBuilder
	{
		// TODO: Implement equals() method.

		return $this->builder;
	}

	public function greaterThan(string $paramName): SelectQueryBuilder
	{
		// TODO: Implement greaterThan() method.

		return $this->builder;
	}

	public function lessThan(string $paramName): SelectQueryBuilder
	{
		// TODO: Implement lessThan() method.

		return $this->builder;
	}

	public function like(string $paramName): SelectQueryBuilder
	{
		// TODO: Implement like() method.

		return $this->builder;
	}

	public function in(string $paramName): SelectQueryBuilder
	{
		// TODO: Implement in() method.

		return $this->builder;
	}
}
