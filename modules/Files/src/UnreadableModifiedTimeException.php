<?php
declare(strict_types=1);

namespace Elephox\Files;

use JetBrains\PhpStorm\Pure;
use Throwable;

class UnreadableModifiedTimeException extends FileException
{
	#[Pure] public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Cannot read modified time of file at $path", $code, $previous);
	}
}
