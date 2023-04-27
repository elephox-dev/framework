<?php
declare(strict_types=1);

namespace Elephox\Console\Command;

use Elephox\Console\Command\Contract\CommandHandler;

readonly class CommandMetadata
{
	public function __construct(
		public CommandTemplate $template,
		public CommandHandler $handler,
	) {
	}

	public function handle(RawCommandInvocation $invocation): int
	{
		$commandInvocation = $invocation->apply($this->template);

		$result = $this->handler->handle($commandInvocation);

		return $result ?? 0;
	}
}
