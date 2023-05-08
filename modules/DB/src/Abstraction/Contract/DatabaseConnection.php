<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction\Contract;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericEnumerable;
use JetBrains\PhpStorm\Language;

interface DatabaseConnection
{
	public function getTables(): ArrayList;

	/**
	 * @template TRow
	 *
	 * @param string $query
	 *
	 * @return GenericEnumerable<TRow>
	 */
	public function query(#[Language("SQL")] string $query): GenericEnumerable;

	/**
	 * @return int<0,max>|numeric-string
	 */
	public function execute(#[Language("SQL")] string $query, ?array $params = null): int|string;
}
