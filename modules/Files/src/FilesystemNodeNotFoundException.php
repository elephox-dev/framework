<?php
declare(strict_types=1);

namespace Elephox\Files;

use JetBrains\PhpStorm\Pure;
use Throwable;

class FilesystemNodeNotFoundException extends FileException
{
	#[Pure]
	public function __construct(string $path, string $nodeName = 'Filesystem node', int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("$nodeName at $path not found", $code, $previous);
	}
}
