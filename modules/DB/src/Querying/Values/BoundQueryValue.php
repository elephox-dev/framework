<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Values;

use Elephox\DB\Querying\Contract;

final readonly class BoundQueryValue implements Contract\QueryValue
{
	public function __construct(
		public string $name,
	) {
	}

	public function getValue(): string
	{
		return $this->name;
	}

	public function __toString(): string
	{
		return '?';
	}
}
