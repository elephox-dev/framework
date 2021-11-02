<?php

namespace Philly\DI;

use Exception;
use JetBrains\PhpStorm\Pure;
use Throwable;

class BindingException extends Exception
{
	#[Pure] public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
