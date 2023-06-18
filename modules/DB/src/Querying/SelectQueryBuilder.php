<?php
declare(strict_types=1);

namespace Elephox\DB\Querying;

use Elephox\DB\Querying\Values\AnyColumnQueryValue;
use Elephox\DB\Querying\Values\ColumnReferenceQueryValue;
use Elephox\DB\Querying\Values\TableReferenceQueryValue;
use InvalidArgumentException;

final readonly class SelectQueryBuilder implements Contract\SelectQueryBuilder
{
	public function __construct(
		private array $columns,
		private ?string $from = null,
		private ?string $alias = null,
		private ?Contract\ExpressionBuilder $where = null,
	) {
	}

	public function from(string $table, ?string $alias = null): Contract\SelectQueryBuilder
	{
		return new self($this->columns, $table, $alias);
	}

	public function where(string $columnName): Contract\ExpressionBuilder
	{
		return new ExpressionBuilder($this, new ColumnReferenceQueryValue($columnName, $this->from));
	}

	public function build(): Contract\ResultSetQuery
	{
		if ($this->from === null) {
			throw new InvalidArgumentException('from must be set');
		}

		if (count($this->columns) === 1 && $this->columns[0] === '*') {
			$columns = [new AnyColumnQueryValue()];
		} else {
			$columns = collect($this->columns)->select(fn (string $col) => new ColumnReferenceQueryValue($col, $this->from));
		}

		$froms = [
			new TableReferenceQueryValue($this->from),
		];

		if ($this->alias !== null) {
			$froms[] = new TableReferenceQueryValue($this->alias);
		}

		$wheres = [];
		if ($this->where !== null) {
			$wheres[] = $this->where->build();
		}

		return new ResultSetQuery(new QueryDefinition('SELECT', [
			...$columns,
			new QueryDefinition('FROM', $froms),
			...$wheres,
		]));
	}
}
