<?php
declare(strict_types=1);

namespace Elephox\Core;

use JetBrains\PhpStorm\Immutable;
use JetBrains\PhpStorm\Pure;

#[Immutable]
class CustomActionType implements Contract\ActionType
{
	/**
	 * @param non-empty-string $name
	 */
	public function __construct(
		private string $name,
		private bool $matchesAny = false,
	) {
	}

	#[Pure]
	public function getName(): string
	{
		return $this->name;
	}

	#[Pure]
	public function matchesAny(): bool
	{
		return $this->matchesAny;
	}
}
