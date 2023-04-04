<?php
declare(strict_types=1);

namespace Elephox\Files;

use Elephox\Files\Contract\Link as LinkContract;
use JetBrains\PhpStorm\Pure;
use Throwable;

class LinkNotFoundException extends FilesystemNodeNotFoundException
{
	#[Pure]
	public function __construct(public readonly ?LinkContract $node, string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($path, 'Link', $code, $previous);
	}
}
