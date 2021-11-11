<?php
declare(strict_types=1);

namespace Philly\Http\Contract;

interface RequestMethod
{
	/**
	 * @return non-empty-string
	 */
	public function getValue(): string;

	public function canHaveBody(): bool;
}
