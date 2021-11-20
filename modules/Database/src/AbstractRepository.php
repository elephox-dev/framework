<?php
declare(strict_types=1);

namespace Elephox\Database;

use Elephox\Collection\ArrayList;
use Elephox\DI\Contract\Container;

/**
 * @template T of Contract\Entity
 *
 * @template-implements Contract\Repository<T>
 */
abstract class AbstractRepository implements Contract\Repository
{
	/**
	 * @param class-string<T> $entityClass
	 * @param Contract\Storage $storage
	 * @param Container $container
	 */
	public function __construct(
		private string $entityClass,
		private Contract\Storage $storage,
		private Container $container,
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

	/**
	 * @param null|callable(T): bool $filter
	 * @return T|null
	 */
	public function first(?callable $filter = null): mixed
	{
		return $this->findAll()->first($filter);
	}

	/**
	 * @param null|callable(T): bool $filter
	 * @return bool
	 */
	public function any(?callable $filter = null): bool
	{
		return $this->findAll()->any($filter);
	}

	/**
	 * @param callable(T): bool $filter
	 * @return ArrayList<T>
	 */
	public function where(callable $filter): ArrayList
	{
		return $this->findAll()->where($filter);
	}

	/**
	 * @param T $value
	 * @return bool
	 */
	public function contains(mixed $value): bool
	{
		return $this->findAll()->contains($value);
	}

	public function find(int|string $id): Contract\Entity
	{
		return $this->where(static fn (Contract\Entity $entity) => $entity->getUniqueId() === $id)->first();
	}

	/**
	 * @return ArrayList<T>
	 */
	public function findAll(): ArrayList
	{
		/** @var ArrayList<array<string, mixed>> $entities */
		$entities = ArrayList::fromArray($this->storage->all($this->entityClass));

		return $entities->map(function (array $entity): Contract\Entity {
			return $this->container->restore($this->getEntityClass(), $entity);
		});
	}

	public function add(Contract\Entity $entity): void
	{
		if (!$entity instanceof ProxyEntity) {
			$entity = new ProxyEntity($entity);
		}
		/** @var ProxyEntity $entity */

		$this->storage->set($this->entityClass, (string)$entity->getUniqueId(), $entity->_proxyGetArrayCopy());
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

		$this->storage->set($this->entityClass, (string)$entity->getUniqueId(), $entity->_proxyGetArrayCopy());

		$entity->_proxyResetDirty();
	}

	public function delete(Contract\Entity $entity): void
	{
		$this->storage->delete($this->entityClass, (string)$entity->getUniqueId());
	}
}
