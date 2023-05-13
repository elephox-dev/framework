<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\DB\Abstraction\Contract\DatabaseConnection;
use Elephox\DB\Abstraction\Contract\QueryAdapter;
use mysqli;

final readonly class MysqlConnection implements DatabaseConnection
{
	public function __construct(
		public mysqli $mysqli,
	) {
	}

	public function getAdapter(): QueryAdapter
	{
		return new MysqlQueryAdapter($this->mysqli);
	}
}
