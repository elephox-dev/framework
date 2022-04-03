<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Console\Command\Contract\CommandHandler;

class CompiledCommandHandler
{
	public function __construct(
		public readonly RawCommandInvocation $invocation,
		public readonly CommandTemplate $template,
		public readonly CommandHandler $handler,
	)
	{
	}
}
