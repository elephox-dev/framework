<?php
declare(strict_types=1);

namespace Elephox\DB\Querying;

readonly class QueryStarter implements Contract\QueryStarter
{
	protected function __construct(
		private Contract\QueryDefinition $rootDefinition,
	) {
	}

	public function build(): Contract\Query
	{
		return new Query($this->rootDefinition);
	}
}
