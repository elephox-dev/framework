<?php
declare(strict_types=1);

namespace Elephox\Files;

use JetBrains\PhpStorm\Pure;
use Throwable;

class DirectoryCouldNotBeScannedException extends FileException
{
	#[Pure]
	public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct(sprintf('Directory "%s" could not be scanned.', $path), $code, $previous);
	}
}
