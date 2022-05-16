<?php
declare(strict_types=1);

namespace Elephox\Support\Contract;

interface ErrorHandler
{
	public function handleError(int $severity, string $message, string $file, int $line): bool;
}
