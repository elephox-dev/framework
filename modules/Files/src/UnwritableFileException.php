<?php
declare(strict_types=1);

namespace Elephox\Files;

use Throwable;

class UnwritableFileException extends FileException
{
	public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Unable to write to file at $path", $code, $previous);
	}
}
