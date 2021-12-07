<?php
declare(strict_types=1);

namespace Elephox\Core\Handler;

class CustomActionType implements Contract\ActionType
{
	/**
	 * @param non-empty-string $name
	 */
	public function __construct(
		private string $name,
		private bool $matchesAny = false,
	)
	{
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function matchesAny(): bool
	{
		return $this->matchesAny;
	}
}
