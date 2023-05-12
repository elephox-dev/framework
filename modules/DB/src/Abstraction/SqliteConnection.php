<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericEnumerable;
use Elephox\Collection\Enumerable;
use Elephox\DB\Abstraction\Contract\DatabaseConnection;
use Elephox\DB\Abstraction\Contract\QueryAdapter;
use Elephox\DB\Querying\Contract\QueryDefinition;
use Elephox\DB\Querying\Contract\QueryParameters;
use SQLite3;
use Throwable;

readonly class SqliteConnection implements DatabaseConnection
{
	public function __construct(
		public SQLite3 $sqlite,
	) {
	}

	public function query(QueryDefinition $query): GenericEnumerable
	{
		return new Enumerable(function () use ($query) {
			try {
				$result = $this->sqlite->query($query);
			} catch (Throwable $t) {
				throw $this->queryFailed($t);
			}

			if ($result === false) {
				throw $this->queryFailed();
			}

			$result->reset();

			while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
				yield $row;
			}

			$result->finalize();
		});
	}

	public function execute(QueryDefinition $query, ?QueryParameters $params = null): int|string
	{
		$stmt = $this->sqlite->prepare($query);

		try {
			$result = $stmt->execute();
		} catch (Throwable $t) {
			throw $this->queryFailed($t);
		}

		if ($result === false) {
			throw $this->queryFailed();
		}
	}

	private function queryFailed(?Throwable $previous = null): QueryException
	{
		return new QueryException('Failed to execute query: ' . $this->sqlite->lastErrorMsg(), $this->sqlite->lastErrorCode(), $previous);
	}

	public function getAdapter(): QueryAdapter
	{
		// TODO: Implement getAdapter() method.
	}
}
