<?php
declare(strict_types=1);

namespace Elephox\Core\Context\Contract;

use Throwable;

interface ExceptionContext extends Context
{
	public function getException(): Throwable;
}
