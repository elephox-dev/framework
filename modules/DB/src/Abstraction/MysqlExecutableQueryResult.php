<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\DB\Querying\Contract\ExecutableQueryResult;

class MysqlExecutableQueryResult implements ExecutableQueryResult
{
	public function __construct(
		public int|string $affectedRows,
	) {
	}

	public function getNumRows(): int|string
	{
		return $this->affectedRows;
	}
}
