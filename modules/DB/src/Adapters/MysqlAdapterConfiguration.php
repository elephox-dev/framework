<?php
declare(strict_types=1);

namespace Elephox\DB\Adapters;

use Elephox\DB\Adapters\Contract\AdapterConfiguration;

readonly class MysqlAdapterConfiguration implements AdapterConfiguration
{
	public function __construct(
		public ?string $host,
		public ?int $port,
		public ?string $database,
		public ?string $user,
		public ?string $password,
		public ?string $socket
	)
	{
	}
}
