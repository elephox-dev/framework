<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Files\Contract\FilesystemNode;
use JetBrains\PhpStorm\Pure;
use Throwable;

class FilesystemNodeNotImplementedException extends FileException
{
	#[Pure] public function __construct(public readonly FilesystemNode $node, string $message, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
