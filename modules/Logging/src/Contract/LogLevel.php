<?php
declare(strict_types=1);

namespace Elephox\Logging\Contract;

interface LogLevel
{
	public function getLevel(): int;

	/**
	 * @return non-empty-string
	 */
	public function getName(): string;
}
