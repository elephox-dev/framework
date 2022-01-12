<?php
declare(strict_types=1);

namespace Elephox\Stream;

use Elephox\Files\FileException;
use JetBrains\PhpStorm\Pure;
use Throwable;

class ReadOnlyFileException extends FileException
{
	#[Pure] public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Unable to write to file at $path", $code, $previous);
	}
}
