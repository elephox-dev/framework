<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\DB\Abstraction\Contract\AdapterConfiguration;

readonly class MysqlAdapterConfiguration implements AdapterConfiguration
{
	public function __construct(
		public ?string $host = null,
		public ?int $port = null,
		public ?string $database = null,
		public ?string $user = null,
		public ?string $password = null,
		public ?string $socket = null,
	) {
	}
}
