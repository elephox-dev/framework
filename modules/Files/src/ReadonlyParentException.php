<?php
declare(strict_types=1);

namespace Elephox\Files;

use JetBrains\PhpStorm\Pure;
use Throwable;

class ReadonlyParentException extends FileException
{
	#[Pure]
	public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Cannot create a file since the parent directory is readonly. Path: $path", $code, $previous);
	}
}
