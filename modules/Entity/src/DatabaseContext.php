<?php
declare(strict_types=1);

namespace Elephox\Entity;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use Elephox\DI\Contract\Container;
use Elephox\Entity\Contract\Configuration;
use Elephox\Entity\Contract\EntitySet;
use LogicException;
use WeakReference;

abstract class DatabaseContext implements Contract\DatabaseContext
{
	private ?WeakReference $connectionReference = null;
	private Configuration $configuration;

	public function __construct(
		protected Container $appContainer,
		protected Contract\EntitySetContainer $entitySetContainer,
	) {
	}

	public function configure(Configuration $configuration): void
	{
		$this->configuration = $configuration;
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function getConnection(): Connection
	{
		/** @var Connection|null $connection */
		$connection = $this->connectionReference?->get();
		if ($connection === null) {
			$connection = DriverManager::getConnection($this->configuration->toArray());
			$this->connectionReference = WeakReference::create($connection);
		}

		return $connection;
	}

	/**
	 * @template T
	 *
	 * @param class-string<T> $entityClass
	 * @return Contract\EntitySet<T>
	 */
	protected function getEntitySet(string $entityClass): EntitySet
	{
		try {
			$entitySet = $this->{$entityClass};
			if (!$entitySet instanceof EntitySet) {
				throw new LogicException("Resolved object is not an instance of EntitySet. Did you register it correctly?");
			}

			return $entitySet;
		} catch (EntitySetException $e) {
			throw new LogicException("EntitySet for $entityClass not found. Did you register it?", previous: $e);
		}
	}

	public function __get(string $entityClass): mixed
	{
		/**
		 * @var non-empty-string $name
		 */
		foreach ([
			$entityClass,
			ucfirst($entityClass . "EntitySet"),
		] as $name) {
			if ($this->entitySetContainer->has($name)) {
				return $this->entitySetContainer->get($name);
			}

			if ($this->appContainer->has($name)) {
				/** @var EntitySet $entitySet */
				$entitySet = $this->appContainer->get($name);
				$this->entitySetContainer->register($name, $entitySet);
				return $entitySet;
			}
		}

		throw new EntitySetException("Unknown property: " . $entityClass);
	}

	public function __set(string $name, array $params): void
	{
		throw new EntitySetException("Unknown property: " . $name);
	}

	public function __isset(string $name): bool
	{
		return false;
	}
}
