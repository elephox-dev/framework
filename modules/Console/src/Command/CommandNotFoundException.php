<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use RuntimeException;
use Throwable;

class CommandNotFoundException extends RuntimeException
{
	public function __construct(string $commandName, int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct("Command '$commandName' not found", $code, $previous);
	}
}
