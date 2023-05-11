<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Values;

use Elephox\DB\Querying\Contract\QueryValue as QueryValueContract;

final readonly class ListQueryValue implements QueryValueContract
{
	/**
	 * @param QueryValueContract $values
	 */
	public function __construct(
		private array $values,
	) {
		assert((function () {
			foreach ($this->values as $value) {
				if (!$value instanceof QueryValueContract) {
					return false;
				}
			}

			return true;
		})(), 'All values must be QueryValue instances');
	}

	public function getValue(): array
	{
		return $this->values;
	}

	public function __toString(): string
	{
		return '(' . implode(', ', $this->getValue()) . ')';
	}
}
