<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
interface RequestMethod
{
	/**
	 * @return non-empty-string
	 */
	public function getValue(): string;

	public function canHaveBody(): bool;
}
