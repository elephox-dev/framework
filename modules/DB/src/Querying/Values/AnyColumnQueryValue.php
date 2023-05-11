<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Values;

use Elephox\DB\Querying\Contract\QueryValue;

final readonly class AnyColumnQueryValue implements QueryValue
{
	public function __construct()
	{
	}

	public function getValue(): string
	{
		return '*';
	}

	public function __toString(): string
	{
		return $this->getValue();
	}
}
