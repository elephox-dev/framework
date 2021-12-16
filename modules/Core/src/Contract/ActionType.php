<?php
declare(strict_types=1);

namespace Elephox\Core\Contract;

interface ActionType
{
	public function getName(): string;

	public function matchesAny(): bool;
}
