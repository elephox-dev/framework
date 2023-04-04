<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Files\Contract\File as FileContract;
use JetBrains\PhpStorm\Pure;
use Throwable;

class FileNotFoundException extends FilesystemNodeNotFoundException
{
	#[Pure]
	public function __construct(public readonly ?FileContract $node, string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($path, 'File', $code, $previous);
	}
}
