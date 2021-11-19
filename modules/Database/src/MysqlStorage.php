<?php
declare(strict_types=1);

namespace Elephox\Database;

use mysqli;
use mysqli_result;

/**
 * @template T of Contract\Entity
 *
 * @template-implements Contract\Storage<T>
 */
class MysqlStorage implements Contract\Storage
{
	public function __construct(
		private mysqli $connection,
		private string $tableName,
	)
	{
	}

	/**
	 * @param string $key
	 * @return array|null
	 */
	public function get(string $key): null|array
	{
		/** @var mysqli_result $query */
		$query = $this->connection->query("SELECT * FROM $this->tableName WHERE id = $key");

		$result = $query->fetch_row();
		if (empty($result)) {
			return null;
		}

		return $result;
	}

	public function set(string $key, array $values): void
	{
		$params = implode(', ', array_map(static fn($key) => "$key = '$values[$key]'", array_filter(array_keys($values), static fn($key) => $key !== "id")));

		$this->connection->query("UPDATE $this->tableName SET $params WHERE id = $key");
	}

	public function add(array $values): string
	{
		$params = implode(', ', array_map(static fn($key) => "$key = '$values[$key]'", array_filter(array_keys($values), static fn($key) => $key !== "id")));

		$this->connection->query("INSERT INTO $this->tableName SET $params");

		return (string)$this->connection->insert_id;
	}

	public function delete(string $key): void
	{
		$this->connection->query("DELETE FROM $this->tableName WHERE id = $key");
	}

	public function exists(string $key): bool
	{
		/** @var mysqli_result $query */
		$query = $this->connection->query("SELECT COUNT(*) FROM $this->tableName WHERE id = $key");

		return $query->num_rows > 0;
	}

	public function all(): array
	{
		/** @var mysqli_result $query */
		$query = $this->connection->query('SELECT * FROM ' . $this->tableName);

		/** @var array<string, array<string, mixed>> $result */
		$result = $query->fetch_all(MYSQLI_ASSOC);
		if (!$result) {
			return [];
		}

		return $result;
	}
}
