<?php
declare(strict_types=1);

namespace Elephox\Files;

use JetBrains\PhpStorm\Pure;
use Throwable;

class LinkLoopDetectedException extends FileException
{
	#[Pure]
	public function __construct(string $aPath, string $bPath, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct(sprintf('Symlink loop detected between (%s) and (%s)', $aPath, $bPath), $code, $previous);
	}
}
