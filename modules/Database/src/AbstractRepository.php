<?php
declare(strict_types=1);

namespace Elephox\Database;

use Elephox\Collection\Contract\GenericCollection;
use Elephox\Collection\Contract\GenericList;

/**
 * @template T of Entity
 *
 * @template-implements Contract\Repository<T>
 */
abstract class AbstractRepository implements Contract\Repository
{
	/**
	 * @param class-string<T> $entityClass
	 */
	public function __construct(
		private string $entityClass
	)
	{
	}

	/**
	 * @return class-string<T>
	 */
	public function getEntityClass(): string
	{
		return $this->entityClass;
	}

	public function first(?callable $filter = null): mixed
	{
		// TODO: Implement first() method.
	}

	public function any(?callable $filter = null): bool
	{
		// TODO: Implement any() method.
	}

	public function where(callable $filter): GenericCollection
	{
		// TODO: Implement where() method.
	}

	public function contains(mixed $value): bool
	{
		// TODO: Implement contains() method.
	}

	public function find(int|string $id): Contract\Entity
	{
		// TODO: Implement find() method.
	}

	public function findAll(): GenericList
	{
		// TODO: Implement findAll() method.
	}

	public function add(Contract\Entity $entity): void
	{
		// TODO: Implement add() method.
	}

	public function update(Contract\Entity $entity): void
	{
		// TODO: Implement update() method.
	}

	public function delete(Contract\Entity $entity): void
	{
		// TODO: Implement delete() method.
	}
}
