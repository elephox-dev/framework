<?php
declare(strict_types=1);

namespace Elephox\Core\Handler\Contract;

interface ActionType
{
	public function getName(): string;

	public function matchesAny(): bool;
}
