<?php
declare(strict_types=1);

namespace Elephox\Http\Contract;

interface HeaderName
{
	/**
	 * @return non-empty-string
	 */
	public function getValue(): string;

	public function canBeDuplicate(): bool;

	public function isOnlyRequest(): bool;

	public function isOnlyResponse(): bool;
}
