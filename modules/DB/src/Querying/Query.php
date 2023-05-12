<?php
declare(strict_types=1);

namespace Elephox\DB\Querying;

use Elephox\DB\Querying\Contract\BoundQuery as BoundQueryContract;
use Elephox\DB\Querying\Contract\QueryDefinition as QueryDefinitionContract;
use Elephox\DB\Querying\Contract\QueryParameters;

readonly class Query implements Contract\Query
{
	public function __construct(
		private QueryDefinitionContract $definition,
	) {
	}

	public function getDefinition(): QueryDefinitionContract
	{
		return $this->definition;
	}

	public function bind(QueryParameters $parameters): BoundQueryContract
	{
		return new BoundQuery($this, $parameters);
	}

	public function __toString(): string
	{
		return (string) $this->definition;
	}
}
