<?php
declare(strict_types=1);

namespace Elephox\Files;

use JetBrains\PhpStorm\Pure;
use Throwable;

class FileCopyException extends FileException
{
	#[Pure] public function __construct(string $path, string $destination, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Failed to copy $path to $destination", $code, $previous);
	}
}
