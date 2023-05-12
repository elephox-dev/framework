<?php
declare(strict_types=1);

namespace Elephox\DB\Abstraction;

use Elephox\DB\Abstraction\Contract\QueryAdapter;
use Elephox\DB\Querying\Contract\BoundQuery;
use Elephox\DB\Querying\Contract\ExecutableQuery;
use Elephox\DB\Querying\Contract\QueryDefinition;
use Elephox\DB\Querying\Contract\QueryExpression;
use Elephox\DB\Querying\Contract\QueryResult;
use Elephox\DB\Querying\Contract\QueryValue;
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
			default => throw new InvalidArgumentException('No corresponding type found for ' . get_debug_type($value)),
		};
	}

	public function __construct(
		private mysqli $mysqli,
	) {
	}

	public function run(BoundQuery $query): QueryResult
	{
		$sql = '';
		$parameters = [];
		$parameterTypes = '';

		$buildExpression = static function (QueryExpression $e, callable $def, callable $exp) use (&$sql, &$parameters, &$parameterTypes): void {
			$sql .= '(';
			$left = $e->getLeft();
			if ($left instanceof QueryValue) {
				$sql .= '?';
				$value = $left->getValue();
				$parameters[] = $value;
				$parameterTypes .= self::getBindParameterType($value);
			} elseif ($left instanceof QueryExpression) {
				$exp($left, $def, $exp);
			} else {
				throw new InvalidArgumentException('left must be QueryValue or QueryExpression, got ' . get_debug_type($left));
			}

			$right = $e->getRight();
			if ($right instanceof QueryValue) {
				$sql .= '?';
				$value = $right->getValue();
				$parameters[] = $value;
				$parameterTypes .= self::getBindParameterType($value);
			} elseif ($right instanceof QueryExpression) {
				$exp($right, $def, $exp);
			} else {
				throw new InvalidArgumentException('right must be QueryValue or QueryExpression, got ' . get_debug_type($right));
			}

			$sql .= ')';
		};

		$buildDefinition = static function (QueryDefinition $d, callable $def, callable $exp) use (&$sql, &$parameters, &$parameterTypes): void {
			$sql .= $d->getVerb() . ' ';

			foreach ($d->getParams() as $param) {
				if ($param instanceof QueryDefinition) {
					$def($param, $def, $exp);
				} elseif ($param instanceof QueryValue) {
					$sql .= '?';
					$value = $param->getValue();
					$parameters[] = $value;
					$parameterTypes .= self::getBindParameterType($value);
				} elseif (is_string($param)) {
					$sql .= '?';
					$parameters[] = $param;
					$parameterTypes .= 's';
				} elseif ($param instanceof QueryExpression) {
					$exp($param, $def, $exp);
				} else {
					throw new InvalidArgumentException('param must be QueryDefinition, QueryValue, QueryExpression or string, got ' . get_debug_type($param));
				}
			}
		};

		$sourceQuery = $query->getQuery();
		$buildDefinition($sourceQuery->getDefinition(), $buildDefinition, $buildExpression);

		$stmt = $this->mysqli->prepare($sql);
		$stmt->bind_param($parameterTypes, ...$parameters);

		$success = $stmt->execute($parameters);
		if ($success === false) {
			throw new QueryException('Query failed: ' . $this->mysqli->error, $this->mysqli->errno);
		}

		if ($sourceQuery instanceof ExecutableQuery) {
			return new MysqlExecutableQueryResult($this->mysqli->affected_rows);
		}

		$result = $stmt->get_result();
		if ($result === false) {
			throw new QueryException('Query failed: ' . $this->mysqli->error, $this->mysqli->errno);
		}

		return new MysqlResultSetQueryResult($result);
	}
}
