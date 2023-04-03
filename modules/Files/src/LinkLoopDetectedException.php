<?php
declare(strict_types=1);

namespace Elephox\Files;

use JetBrains\PhpStorm\Pure;
use Throwable;

class LinkLoopDetectedException extends FileException
{
	#[Pure]
	public function __construct(Contract\Link $a, Contract\Link $b, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct(sprintf('Symlink loop detected between (%s) and (%s)', $a->path(), $b->path()), $code, $previous);
	}
}
