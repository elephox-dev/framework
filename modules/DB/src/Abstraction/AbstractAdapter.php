<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\DB\Abstraction\Contract\AdapterConfiguration;
use Elephox\DB\Abstraction\Contract\DatabaseAdapter;
use Elephox\DB\Abstraction\Contract\DatabaseConnection;

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
