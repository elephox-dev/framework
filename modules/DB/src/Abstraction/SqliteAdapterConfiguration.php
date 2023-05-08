<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\DB\Abstraction\Contract\AdapterConfiguration;

readonly class SqliteAdapterConfiguration implements AdapterConfiguration
{
	public function __construct(
		public ?string $path = null,
	) {}
}
