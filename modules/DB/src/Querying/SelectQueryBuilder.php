<?php
declare(strict_types=1);

namespace Elephox\DB\Querying;

final readonly class SelectQueryBuilder implements Contract\SelectQueryBuilder
{
	public function __construct(
		private array $columns,
		private ?string $from = null,
		private ?string $alias = null,
		private ?Contract\ExpressionBuilder $where = null,
	) {}

	public function from(string $table, ?string $alias = null): Contract\SelectQueryBuilder {
		return new self($this->columns, $table, $alias);
	}

	public function where(string $columnName): Contract\ExpressionBuilder {
		return new ExpressionBuilder($this, $columnName);
	}

	public function build(): Contract\ResultSetQuery {
		// TODO: Implement build() method.
	}
}
