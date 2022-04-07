<?php
declare(strict_types=1);

namespace Elephox\Files;

use Throwable;

class FileAlreadyExistsException extends FileException
{
	public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("File already exists: $path", $code, $previous);
	}
}
