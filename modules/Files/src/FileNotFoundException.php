<?php
declare(strict_types=1);

namespace Elephox\Files;

use JetBrains\PhpStorm\Pure;
use Throwable;

class FileNotFoundException extends FileException
{
	#[Pure] public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("The file at $path was not found", $code, $previous);
	}
}
