<?php
declare(strict_types=1);

namespace Elephox\Database;

use mysqli;

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

	public function get(string $key): array
	{
		return $this->connection->query("SELECT * FROM $this->tableName WHERE id = $key")->fetch_assoc()[0];
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

		return $this->connection->insert_id;
	}

	public function delete(string $key): void
	{
		$this->connection->query("DELETE FROM $this->tableName WHERE id = $key");
	}

	public function exists(string $key): bool
	{
		return $this->connection->query("SELECT COUNT(*) FROM $this->tableName WHERE id = $key")->num_rows > 0;
	}

	public function all(): array
	{
		return $this->connection->query('SELECT * FROM ' . $this->tableName)->fetch_assoc();
	}
}
