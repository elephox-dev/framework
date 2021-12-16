<?php
declare(strict_types=1);

namespace Elephox\Core\Contract;

interface HandlerStackMeta
{
	public function getType(): ActionType;

	public function getWeight(): int;
}
