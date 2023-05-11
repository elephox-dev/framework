<?php
declare(strict_types=1);

namespace Elephox\DB\Querying;

readonly class QueryStarter implements Contract\QueryStarter
{
	public function select(iterable|string $columns): Contract\SelectQueryBuilder {
		if (is_string($columns)) {
			$columns = [$columns];
		} else {
			$columns = iterator_to_array($columns);
		}

		return new SelectQueryBuilder($columns);
	}

	public function insert(string $into): Contract\InsertQueryBuilder {
		// TODO: Implement insert() method.
	}
}
