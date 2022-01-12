<?php
declare(strict_types=1);

namespace Elephox\Files;

use Throwable;

class FileDeleteException extends FileException
{
	public function __construct(string $path, int $code = 0, ?Throwable $previous = null) {
		parent::__construct("Could not delete file: $path", $code, $previous);
	}
}
