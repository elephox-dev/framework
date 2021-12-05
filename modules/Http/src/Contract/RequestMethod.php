<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Pure;

interface RequestMethod
{
	/**
	 * @return non-empty-string
	 */
	#[Pure] public function getValue(): string;

	#[Pure] public function canHaveBody(): bool;
}
