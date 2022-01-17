<?php
declare(strict_types=1);

namespace Elephox\Core\Contract;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
interface ActionType
{
	#[Pure]
	public function getName(): string;

	#[Pure]
	public function matchesAny(): bool;
}
