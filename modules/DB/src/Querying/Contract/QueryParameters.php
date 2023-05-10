<?php
declare(strict_types=1);

namespace Elephox\DB\Querying\Contract;

use Elephox\Collection\Contract\GenericReadonlyList;
use IteratorAggregate;

interface QueryParameters extends IteratorAggregate
{
	/**
	 * @returns GenericReadonlyList<QueryParameter>
	 */
	public function toList(): GenericReadonlyList;

	public function add(QueryParameter ...$parameter): self;

	public function put(string $name, mixed $value): self;

	public function has(string $name): bool;

	public function get(string $name): QueryParameter;

	public function remove(string $name): self;
}
