<?php
declare(strict_types=1);

namespace Elephox\Entity;

class ChangeUnit implements Contract\ChangeUnit
{
	public function __construct(
		private ChangeAction $action,
		private ?string $property,
		private mixed $oldValue,
		private mixed $newValue
	)
	{
	}

	public function getAction(): ChangeAction
	{
		return $this->action;
	}


	public function getPropertyName(): ?string
	{
		return $this->property;
	}

	public function getOldValue(): mixed
	{
		return $this->oldValue;
	}

	public function getNewValue(): mixed
	{
		return $this->newValue;
	}
}
