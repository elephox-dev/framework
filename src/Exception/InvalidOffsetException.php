<?php

namespace Philly\Base\Exception;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidOffsetException extends InvalidArgumentException
{
    #[Pure] public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
