<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Values;

use Closure;
use Elephox\DB\Querying\Contract;

final readonly class BoundQueryValue implements Contract\QueryValue
{
	public function __construct(
		public string $name,
		private Closure $callback,
	) {
	}

	public function getValue(): Closure
	{
		return $this->callback;
	}

	public function __toString(): string
	{
		return '?';
	}
}
