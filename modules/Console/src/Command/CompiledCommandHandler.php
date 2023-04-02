<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Console\Command\Contract\CommandHandler;

readonly class CompiledCommandHandler
{
	public function __construct(
		public RawCommandInvocation $invocation,
		public CommandTemplate $template,
		public CommandHandler $handler,
	) {
	}
}
