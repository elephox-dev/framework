<?php
declare(strict_types=1);

namespace Elephox\Database\Contract;

interface Entity
{
	public function getUniqueIdProperty(): string;
	public function getUniqueId(): null|string|int;
}
