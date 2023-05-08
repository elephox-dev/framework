<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction\Contract;

/**
 * @template TConfig of AdapterConfiguration
 * @template TConnection of DatabaseConnection
 */
interface DatabaseAdapter
{
	/**
	 * @return TConfig
	 */
	public function getConfiguration(): AdapterConfiguration;

	/**
	 * @return TConnection
	 */
	public function connect(): DatabaseConnection;

	/**
	 * @param TConnection $connection
	 */
	public function disconnect(DatabaseConnection $connection): void;
}
