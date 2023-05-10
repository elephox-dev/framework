<?php
declare(strict_types=1);

namespace Elephox\DB\Querying;

use Elephox\DB\Querying\Contract\QueryExpression;
use Elephox\DB\Querying\Contract\QueryValue;

final readonly class QueryDefinition implements Contract\QueryDefinition
{
	public function __construct(
		private string $verb,
		private array $params,
	) {
		foreach ($this->params as $param) {
			assert($param instanceof QueryValue || $param instanceof QueryExpression || $param instanceof Contract\QueryDefinition, 'params must be QueryValue, QueryExpression or QueryDefinition instances, got ' . get_debug_type($param));
		}
	}

	public function getVerb(): string
	{
		return $this->verb;
	}

	public function getParams(): array
	{
		return $this->params;
	}

	public function __toString(): string
	{
		return rtrim($this->getVerb() . ' ' . implode(' ', $this->getParams()));
	}
}
