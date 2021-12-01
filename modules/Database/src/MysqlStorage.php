<?php
declare(strict_types=1);

namespace Elephox\Database;

use mysqli;
use mysqli_result;

class MysqlStorage implements Contract\Storage
{
	public function __construct(
		private mysqli $connection,
	)
	{
	}

	public function getConnection(): mysqli
	{
		return $this->connection;
	}

	public function get(string $entityName, string $key): null|array
	{
		/** @var mysqli_result $query */
		$query = $this->connection->query("SELECT * FROM $entityName WHERE id = $key");

		$result = $query->fetch_row();
		if (empty($result)) {
			return null;
		}

		return $result;
	}

	public function set(string $entityName, string $key, array $values): void
	{
		$params = implode(', ', array_map(static fn($key) => "$key = '$values[$key]'", array_filter(array_keys($values), static fn($key) => $key !== "id")));

		$this->connection->query("UPDATE $entityName SET $params WHERE id = $key");
	}

	public function add(string $entityName, array $values): string
	{
		$params = implode(', ', array_map(static fn($key) => "$key = '$values[$key]'", array_filter(array_keys($values), static fn($key) => $key !== "id")));

		$this->connection->query("INSERT INTO $entityName SET $params");

		return (string)$this->connection->insert_id;
	}

	public function delete(string $entityName, string $key): void
	{
		$this->connection->query("DELETE FROM $entityName WHERE id = $key");
	}

	public function exists(string $entityName, string $key): bool
	{
		/** @var mysqli_result $query */
		$query = $this->connection->query("SELECT COUNT(*) FROM $entityName WHERE id = $key");

		return $query->num_rows > 0;
	}

	public function all(string $entityName): array
	{
		/** @var mysqli_result $query */
		$query = $this->connection->query("SELECT * FROM $entityName");

		/** @var array<string, array<string, mixed>> $result */
		$result = $query->fetch_all(MYSQLI_ASSOC);
		if (!$result) {
			return [];
		}

		return $result;
	}
}
