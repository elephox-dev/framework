<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use InvalidArgumentException;
use Throwable;

class NoCommandInCommandLineException extends InvalidArgumentException
{
	public function __construct(int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct('No command provided', $code, $previous);
	}
}
