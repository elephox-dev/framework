<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Enumerable;
use Elephox\DB\Querying\Contract\ResultSetQueryResult;
use mysqli_result;

class MysqlResultSetQueryResult implements ResultSetQueryResult
{
	public function __construct(
		public mysqli_result $result,
	) {
	}

	public function getNumRows(): int|string
	{
		return $this->result->num_rows;
	}

	public function getResults(): GenericEnumerable
	{
		return Enumerable::from($this->result);
	}
}
