<?php
declare(strict_types=1);

namespace Elephox\Core;

use Elephox\Core\Context\Contract\Context;
use JetBrains\PhpStorm\Pure;
use Throwable;

class UnhandledContextException extends CoreException
{
	#[Pure]
	public function __construct(Context $context, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("No handler found for context " . $context::class, $code, $previous);
	}
}
