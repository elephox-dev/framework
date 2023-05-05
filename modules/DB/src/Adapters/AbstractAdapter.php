<?php
declare(strict_types=1);

namespace Elephox\DB\Adapters;

use Elephox\DB\Adapters\Contract\AdapterConfiguration;
use Elephox\DB\Adapters\Contract\DatabaseAdapter;
use Elephox\DB\Adapters\Contract\DatabaseConnection;

/**
 * @template TConfig of AdapterConfiguration
 * @template TConnection of DatabaseConnection
 *
 * @implements DatabaseAdapter<TConfig, TConnection>
 */
abstract class AbstractAdapter implements DatabaseAdapter
{
	public function __construct(
		protected readonly AdapterConfiguration $configuration,
	)
	{
	}

	/**
	 * @return TConfig
	 */
	public function getConfiguration(): AdapterConfiguration
	{
		return $this->configuration;
	}
}
