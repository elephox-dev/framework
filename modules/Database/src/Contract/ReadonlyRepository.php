<?php
declare(strict_types=1);

namespace Elephox\Database\Contract;

use Elephox\Collection\Contract\Filterable;
use Elephox\Collection\Contract\GenericList;

/**
 * @template T of Entity
 *
 * @extends Filterable<T>
 */
interface ReadonlyRepository extends Filterable
{
	/**
	 * @return T|null
	 */
	public function find(string|int $id): ?Entity;

	/**
	 * @return T|null
	 */
	public function findBy(string $property, mixed $value): ?Entity;

	/**
	 * @return GenericList<T>
	 */
	public function findAll(): GenericList;
}
