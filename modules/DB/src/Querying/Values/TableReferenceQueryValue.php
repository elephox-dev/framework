<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Values;

use Elephox\DB\Querying\Contract\QueryValue;

final readonly class TableReferenceQueryValue implements QueryValue
{
	public function __construct(
		private string $table,
	) {
	}

	public function getValue(): string
	{
		return $this->table;
	}

	public function __toString()
	{
		return $this->getValue();
	}
}
