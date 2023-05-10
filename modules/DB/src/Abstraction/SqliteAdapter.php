<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\DB\Abstraction\Contract\DatabaseConnection;
use SQLite3;

/**
 * @extends AbstractAdapter<SqliteAdapterConfiguration>
 */
class SqliteAdapter extends AbstractAdapter
{
	public function __construct(SqliteAdapterConfiguration $configuration)
	{
		parent::__construct($configuration);
	}

	public function connect(): DatabaseConnection
	{
		$sqlite = new SQLite3($this->getConfiguration()->path);
		$sqlite->enableExceptions(true);

		return new SqliteConnection($sqlite);
	}

	public function disconnect(DatabaseConnection $connection): void
	{
		// TODO: Implement disconnect() method.
	}
}
