<?php
declare(strict_types=1);

namespace Elephox\Entity\Contract;

use Elephox\Entity\ChangeAction;

interface ChangeUnit
{
	public function getAction(): ChangeAction;

	public function getPropertyName(): ?string;

	public function getOldValue(): mixed;

	public function getNewValue(): mixed;
}
