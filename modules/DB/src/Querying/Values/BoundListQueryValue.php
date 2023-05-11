<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Values;

use Closure;
use Elephox\DB\Querying\Contract\QueryValue as QueryValueContract;

final readonly class BoundListQueryValue implements QueryValueContract
{
	public function __construct(
		public string $name,
		private Closure $callback,
	) {
	}

	public function getValue(): Closure
	{
		return function (): array {
			$result = ($this->callback)();

			assert(is_array($result), 'Callback must return an array');

			return $result;
		};
	}

	public function __toString(): string
	{
		return '?';
	}
}
