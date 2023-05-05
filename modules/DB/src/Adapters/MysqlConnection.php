<?php
declare(strict_types=1);

namespace Elephox\DB\Adapters;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Enumerable;
use Elephox\DB\Adapters\Contract\DatabaseConnection;
use mysqli;
use RuntimeException;

if (!extension_loaded('mysqli')) {
	throw new RuntimeException('mysqli extension not loaded');
}

readonly class MysqlConnection implements DatabaseConnection
{
	public function __construct(
		public mysqli $mysqli,
	)
	{
	}

	public function query(string $query): GenericEnumerable
	{
		$result = $this->mysqli->query($query);
		if ($result === false) {
			throw new QueryException("Failed to execute query: " . $this->mysqli->error, $this->mysqli->errno);
		}

		return new Enumerable($result->getIterator());
	}

	public function execute(string $query, ?array $params = null): int|string
	{
		$result = $this->mysqli->execute_query($query, $params);
		if ($result === false) {
			throw new QueryException("Failed to execute query: " . $this->mysqli->error, $this->mysqli->errno);
		}

		return $result->num_rows;
	}
}
