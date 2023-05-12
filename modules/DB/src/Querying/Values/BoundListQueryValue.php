<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Values;

use Closure;
use Elephox\DB\Querying\Contract\QueryValue as QueryValueContract;

final readonly class BoundListQueryValue implements QueryValueContract
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
