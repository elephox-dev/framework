<?php
declare(strict_types=1);

namespace Elephox\Files;

use JetBrains\PhpStorm\Pure;
use Throwable;

class FileNotCreatedException extends FileException
{
	/**
	 * @param string $path
	 */
	#[Pure]
	public function __construct(string $path, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("File '$path' could not be created.", $code, $previous);
	}
}
