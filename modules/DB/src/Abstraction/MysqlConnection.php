<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Enumerable;
use Elephox\DB\Abstraction\Contract\DatabaseConnection;
use JetBrains\PhpStorm\Language;
use mysqli;

readonly class MysqlConnection implements DatabaseConnection
{
	public function __construct(
		public mysqli $mysqli,
	)
	{
	}

	public function query(#[Language("SQL")] string $query): GenericEnumerable
	{
		$result = $this->mysqli->query($query);
		if ($result === false) {
			throw new QueryException("Failed to execute query: " . $this->mysqli->error, $this->mysqli->errno);
		}

		return new Enumerable($result->getIterator());
	}

	public function execute(#[Language("SQL")] string $query, ?array $params = null): int|string
	{
		$result = $this->mysqli->execute_query($query, $params);
		if ($result === false) {
			throw new QueryException("Failed to execute query: " . $this->mysqli->error, $this->mysqli->errno);
		}

		return $result->num_rows;
	}

	public function getTables(): ArrayList {
		return $this->query("SHOW TABLES")->toArrayList();
	}
}
