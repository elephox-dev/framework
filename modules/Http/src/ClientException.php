<?php

namespace Elephox\Http;

use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Throwable;

class ClientException extends RuntimeException
{
	#[Pure] public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
