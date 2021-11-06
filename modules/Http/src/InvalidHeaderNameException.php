<?php
declare(strict_types=1);

namespace Philly\Http;

use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class InvalidHeaderNameException extends InvalidArgumentException
{
	#[Pure] public function __construct(string $headerName, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Invalid header name: $headerName", $code, $previous);
	}
}
