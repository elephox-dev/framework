<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\DB\Abstraction\Contract\QueryAdapter;
use Elephox\DB\Querying\Contract\BoundQuery;
use Elephox\DB\Querying\Contract\ExecutableQuery;
use Elephox\DB\Querying\Contract\QueryParameter;
use Elephox\DB\Querying\Contract\QueryResult;
use InvalidArgumentException;
use mysqli;

final readonly class MysqlQueryAdapter implements QueryAdapter
{
	public static function getBindParameterType(mixed $value): string
	{
		return match (get_debug_type($value)) {
			'int' => 'i',
			'float' => 'd',
			'bool' => 'b',
			'string' => 's',
			default => throw new InvalidArgumentException("No corresponding type found for " . get_debug_type($value)),
		};
	}

	public function __construct(
		private mysqli $mysqli,
	) {
	}

	public function run(BoundQuery $query): QueryResult
	{
		$sourceQuery = $query->getQuery();

		$sql = (string)$sourceQuery;
		$stmt = $this->mysqli->prepare($sql);
		$parameters = [];
		$parameterTypes = "";

		/**
		 * @var QueryParameter $param
		 */
		foreach ($query->getParameters() as $name => $param) {
			$parameters[] = $param->getValue();
			$parameterTypes .= self::getBindParameterType($param->getValue());
		}

		$stmt->bind_param($parameterTypes, ...$parameters);

		$success = $stmt->execute($parameters);
		if ($success === false) {
			throw new QueryException("Query failed: " . $this->mysqli->error, $this->mysqli->errno);
		}

		if ($sourceQuery instanceof ExecutableQuery) {
			return new MysqlExecutableQueryResult($this->mysqli->affected_rows);
		}

		$result = $stmt->get_result();
		if ($result === false) {
			throw new QueryException("Query failed: " . $this->mysqli->error, $this->mysqli->errno);
		}

		return new MysqlResultSetQueryResult($result);
	}
}
