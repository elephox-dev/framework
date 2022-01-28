<?php
declare(strict_types=1);

namespace Elephox\Entity\Contract;

use Doctrine\DBAL\Driver\Connection;

interface DatabaseContext
{
	public function configure(Configuration $configuration): void;

	public function getConnection(): Connection;
}
