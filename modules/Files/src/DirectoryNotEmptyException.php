<?php
declare(strict_types=1);

namespace Elephox\Files;

use Throwable;

class DirectoryNotEmptyException extends FileException
{
	public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Directory $path is not empty", $code, $previous);
	}
}
