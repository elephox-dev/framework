<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Enumerable;
use Elephox\DB\Abstraction\Contract\DatabaseConnection;
use Elephox\DB\Abstraction\Contract\QueryAdapter;
use Elephox\DB\Querying\Contract\ExecutableQuery;
use Elephox\DB\Querying\Contract\ResultSetQuery;
use JetBrains\PhpStorm\Language;
use mysqli;
use mysqli_result;

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
