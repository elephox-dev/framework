<?php
declare(strict_types=1);

namespace Elephox\Core\Middleware\Attribute;

use Elephox\Core\Contract\ActionType;
use JetBrains\PhpStorm\Pure;

abstract class AbstractMiddlewareAttribute implements Contract\MiddlewareAttribute
{
	#[Pure] public function __construct(
		private ActionType $type,
		private int $weight
	) {
	}

	#[Pure] public function getType(): ActionType
	{
		return $this->type;
	}

	#[Pure] public function getWeight(): int
	{
		return $this->weight;
	}
}
