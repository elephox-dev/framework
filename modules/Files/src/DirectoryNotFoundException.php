<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Files\Contract\Directory as DirectoryContract;
use JetBrains\PhpStorm\Pure;
use Throwable;

class DirectoryNotFoundException extends FilesystemNodeNotFoundException
{
	#[Pure]
	public function __construct(public readonly ?DirectoryContract $node, string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($path, 'Directory', $code, $previous);
	}
}
