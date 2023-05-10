<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

interface QueryResult
{
	/**
	 * @return int|numeric-string
	 */
	public function getNumRows(): int|string;
}
