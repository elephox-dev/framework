<?php
declare(strict_types=1);

namespace Elephox\Development\Commands;

use Elephox\Console\Command\CommandInvocation;
use Elephox\Console\Command\CommandTemplateBuilder;
use Elephox\Console\Command\Contract\CommandHandler;

class EchoCommand implements CommandHandler
{
	public function configure(CommandTemplateBuilder $builder): void
	{
		$builder->name('echo');
		$builder->description('Echo a message');
		$builder->required('message', 'The message to echo');
	}

	public function handle(CommandInvocation $command): int|null
	{
		echo $command->getArgument('message')->value . PHP_EOL;

		return 0;
	}
}
