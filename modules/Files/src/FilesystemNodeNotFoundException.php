<?php
declare(strict_types=1);

namespace Elephox\Files;

use JetBrains\PhpStorm\Pure;
use Throwable;

class FilesystemNodeNotFoundException extends FileException
{
	#[Pure]
	public function __construct(public readonly string $path, public readonly string $nodeName = 'Filesystem node', int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("$nodeName at $path not found", $code, $previous);
	}
}
