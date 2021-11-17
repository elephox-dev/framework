<?php
declare(strict_types=1);

namespace Elephox\Database\Contract;

use Elephox\Collection\Contract\Filterable;
use Elephox\Collection\Contract\GenericList;

/**
 * @template T of Entity
 * @template TId of string|int
 *
 * @extends Filterable<T>
 */
interface ReadonlyRepository extends Filterable
{
	/**
	 * @param TId $id
	 * @return T
	 */
	public function find(string|int $id): Entity;

	/**
	 * @return GenericList<T>
	 */
	public function findAll(): GenericList;
}
