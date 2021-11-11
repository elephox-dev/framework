<?php
declare(strict_types=1);

namespace Elephox\DI;

use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidBindingInstanceException extends BindingException
{
	#[Pure] public function __construct(object $instance, string $expected, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct(get_class($instance) . " is not an instance of " . $expected, $code, $previous);
	}
}
