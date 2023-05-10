<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Values;

use Elephox\DB\Querying\Contract\QueryValue;

final readonly class ColumnReferenceQueryValue implements QueryValue
{
	public function __construct(
		public string $table,
		public string $column,
	) {
	}

	public function getValue(): array
	{
		return [$this->table, $this->column];
	}

	public function __toString(): string
	{
		return implode('.', $this->getValue());
	}
}
