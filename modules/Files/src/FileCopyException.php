<?php
declare(strict_types=1);

namespace Elephox\Files;

use Throwable;

class FileCopyException extends FileException
{
	public function __construct(string $path, string $destination, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Failed to copy $path to $destination", $code, $previous);
	}
}
