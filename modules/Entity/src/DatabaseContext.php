<?php
declare(strict_types=1);

namespace Elephox\Entity;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use Elephox\Entity\Contract\Configuration;
use Elephox\Entity\Contract\EntitySet;
use WeakReference;

abstract class DatabaseContext implements Contract\DatabaseContext
{
	private ?WeakReference $connectionReference = null;
	private ?Configuration $configuration;

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
	}
}
