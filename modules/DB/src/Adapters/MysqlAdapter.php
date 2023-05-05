<?php
declare(strict_types=1);

namespace Elephox\DB\Adapters;

use Elephox\DB\Adapters\Contract\DatabaseConnection;
use RuntimeException;

if (!extension_loaded('mysqli')) {
	throw new RuntimeException('mysqli extension not loaded');
}

/**
 * @extends AbstractAdapter<MysqlAdapterConfiguration, MysqlConnection>
 */
class MysqlAdapter extends AbstractAdapter
{
	public function __construct(MysqlAdapterConfiguration $configuration)
	{
		parent::__construct($configuration);
	}

	public function getConfiguration(): MysqlAdapterConfiguration
	{
		return $this->configuration;
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
