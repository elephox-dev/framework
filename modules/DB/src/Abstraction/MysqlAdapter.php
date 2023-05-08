<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\DB\Abstraction\Contract\DatabaseConnection;

/**
 * @extends AbstractAdapter<MysqlAdapterConfiguration, MysqlConnection>
 */
class MysqlAdapter extends AbstractAdapter
{
	public function __construct(MysqlAdapterConfiguration $configuration)
	{
		parent::__construct($configuration);
	}

	public function connect(): MysqlConnection
	{
		$config = $this->getConfiguration();

		$mysqli = mysqli_connect($config->host, $config->user, $config->password, $config->database, $config->port, $config->socket);
		if ($mysqli === false) {
			throw new ConnectException("Failed to establish MySQL connection: " . mysqli_connect_error(), mysqli_connect_errno());
		}

		return new MysqlConnection($mysqli);
	}

	public function disconnect(DatabaseConnection $connection): void
	{
		assert($connection instanceof MysqlConnection);

		mysqli_close($connection->mysqli);
	}
}
