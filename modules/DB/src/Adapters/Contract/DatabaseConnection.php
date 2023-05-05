<?php
declare(strict_types=1);

namespace Elephox\DB\Adapters\Contract;

use Elephox\Collection\Contract\GenericEnumerable;

interface DatabaseConnection
{
	/**
	 * @template TRow
	 *
	 * @param string $query
	 *
	 * @return GenericEnumerable<TRow>
	 */
	public function query(string $query): GenericEnumerable;

	/**
	 * @return int<0,max>|numeric-string
	 */
	public function execute(string $query, ?array $params = null): int|string;
}
