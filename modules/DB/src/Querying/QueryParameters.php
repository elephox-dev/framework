<?php
declare(strict_types=1);

namespace Elephox\DB\Querying;

use ArrayIterator;
use Elephox\Collection\ArrayList;
use Elephox\Collection\Contract\GenericReadonlyList;
use Elephox\DB\Querying\Contract\QueryParameter as QueryParameterContract;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use Traversable;

final class QueryParameters implements Contract\QueryParameters
{
	public static function from(iterable $parameters): self
	{
		if ($parameters instanceof Iterator) {
			$arr = iterator_to_array($parameters);
		} else if ($parameters instanceof IteratorAggregate) {
			$arr = iterator_to_array($parameters->getIterator());
		} else if (is_array($parameters)) {
			if (array_is_list($parameters)) {
				$arr = $parameters;
			} else {
				$arr = [];
				foreach ($parameters as $key => $value) {
					assert(is_string($key));

					$arr[$key] = new QueryParameter($key, $value);
				}
			}
		} else {
			throw new InvalidArgumentException("Invalid parameters type: " . get_debug_type($parameters));
		}

		return new self($arr);
	}

	private array $parameters;

	/**
	 * @param list<QueryParameterContract> $parameters
	 */
	public function __construct(
		array $parameters = [],
	) {
		$this->parameters = [];

		foreach ($parameters as $parameter) {
			if (!$parameter instanceof QueryParameterContract) {
				throw new InvalidArgumentException("Invalid parameter type: " . get_debug_type($parameter));
			}

			$this->add($parameter);
		}
	}

	public function add(QueryParameterContract ...$parameters): QueryParameters
	{
		foreach ($parameters as $parameter) {
			$name = $parameter->getName();

			if ($this->has($name)) {
				throw new InvalidArgumentException("Parameter already exists: " . $name);
			}

			$this->parameters[$name] = $parameter;
		}

		return $this;
	}

	public function put(string $name, mixed $value): QueryParameters
	{
		$parameter = new QueryParameter($name, $value);

		$this->add($parameter);

		return $this;
	}

	public function toList(): GenericReadonlyList
	{
		return ArrayList::from($this->parameters);
	}

	public function has(string $name): bool
	{
		return array_key_exists($name, $this->parameters);
	}

	public function get(string $name): QueryParameterContract
	{
		if (!$this->has($name)) {
			throw new InvalidArgumentException("Parameter not found: " . $name);
		}

		return $this->parameters[$name];
	}

	public function remove(string $name): Contract\QueryParameters
	{
		if (!$this->has($name)) {
			return $this;
		}

		unset($this->parameters[$name]);

		return $this;
	}

	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->parameters);
	}
}
