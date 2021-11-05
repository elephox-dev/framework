<?php

namespace Philly\DI;

use JetBrains\PhpStorm\Pure;
use Throwable;

class BindingNotFoundException extends BindingException
{
	#[Pure] public function __construct(string $contract, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Binding not found for contract '$contract'", $code, $previous);
	}
}
