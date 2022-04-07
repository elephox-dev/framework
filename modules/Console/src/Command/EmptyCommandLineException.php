<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use InvalidArgumentException;
use Throwable;

class EmptyCommandLineException extends InvalidArgumentException
{
	public function __construct(int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct('Command line is empty', $code, $previous);
	}
}
