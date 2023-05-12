<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Values;

use Elephox\DB\Querying\Contract\QueryValue;

final readonly class ColumnReferenceQueryValue implements QueryValue
{
	public function __construct(
		public string $column,
		public ?string $table = null,
	) {
	}

	public function getValue(): array
	{
		return [$this->table, $this->column];
	}

	public function __toString(): string
	{
		$value = $this->getValue();
		if ($value[0] === null) {
			return $value[1];
		}

		return implode('.', $value);
	}
}
