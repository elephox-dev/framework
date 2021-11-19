<?php
declare(strict_types=1);

namespace Elephox\Database;

use Elephox\Collection\ArrayList;

/**
 * @template T of Contract\Entity
 *
 * @template-implements Contract\Repository<T>
 */
abstract class AbstractRepository implements Contract\Repository
{
	/**
	 * @param class-string<T> $entityClass
	 * @param Contract\Storage<T> $storage
	 */
	public function __construct(
		private string $entityClass,
		private Contract\Storage $storage,
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
		return $this->findAll()->first($filter);
	}

	public function any(?callable $filter = null): bool
	{
		return $this->findAll()->any($filter);
	}

	public function where(callable $filter): ArrayList
	{
		return $this->findAll()->where($filter);
	}

	public function contains(mixed $value): bool
	{
		return $this->findAll()->contains($value);
	}

	public function find(int|string $id): Contract\Entity
	{
		return $this->where(static fn (Contract\Entity $entity) => $entity->getUniqueId() === $id)->first();
	}

	public function findAll(): ArrayList
	{
		/** @var ArrayList<array<string, mixed>> $entities */
		$entities = ArrayList::fromArray($this->storage->all());

		return $entities->map(function (array $entity): Contract\Entity {
				/** @var T */
				return ProxyEntity::hydrate($this->entityClass, $entity);
			});
	}

	public function add(Contract\Entity $entity): void
	{
		if (!$entity instanceof ProxyEntity) {
			$entity = new ProxyEntity($entity);
		}
		/** @var ProxyEntity $entity */

		$this->storage->set((string)$entity->getUniqueId(), $entity->_proxyGetArrayCopy());
	}

	public function update(Contract\Entity $entity): void
	{
		if (!$entity instanceof ProxyEntity) {
			$entity = new ProxyEntity($entity, true);
		}
		/** @var ProxyEntity $entity */

		if (!$entity->_proxyIsDirty()) {
			return;
		}

		$this->storage->set((string)$entity->getUniqueId(), $entity->_proxyGetArrayCopy());

		$entity->_proxyResetDirty();
	}

	public function delete(Contract\Entity $entity): void
	{
		$this->storage->delete((string)$entity->getUniqueId());
	}
}
