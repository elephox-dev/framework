<?php
declare(strict_types=1);

namespace Elephox\Database\Contract;

/**
 * @template T of Entity
 *
 * @extends ReadonlyRepository<T>
 */
interface Repository extends ReadonlyRepository
{
	/**
	 * @param T $entity
	 */
	public function add(Entity $entity): void;

	/**
	 * @param T $entity
	 */
	public function update(Entity $entity): void;

	/**
	 * @param T $entity
	 */
	public function delete(Entity $entity): void;
}
