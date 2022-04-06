<?php
declare(strict_types=1);

namespace Elephox\Support\Contract;

use Throwable;

interface ExceptionHandler
{
	public function handleException(Throwable $exception): void;
}
